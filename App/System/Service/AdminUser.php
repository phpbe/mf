<?php
namespace App\System\Service;

use GuzzleHttp\Exception\ServerException;
use Be\System\Be;
use Be\System\Db\Row;
use Be\System\Db\Tuple;
use Be\System\Service;
use Be\System\Service\ServiceException;
use Be\System\Session;
use Be\System\Cookie;
use Be\Util\Random;

class AdminUser extends Service
{

    /**
     * 登录
     * @param string $username 用户名
     * @param string $password 密码
     * @param string $ip IP 地址
     * @return \stdClass
     * @throws \Exception
     */
    public function login($username, $password, $ip)
    {
        $username = trim($username);
        if (!$username) {
            throw new ServiceException('参数用户名（username）缺失！');
        }

        $password = trim($password);
        if (!$password) {
            throw new ServiceException('参数密码（password）缺失！');
        }

        $ip = trim($ip);
        if (!$ip) {
            throw new ServiceException('参数IP（$ip）缺失！');
        }

        $timesKey = 'AdminUser:login:ip:' . $ip;
        $times = Session::get($timesKey);
        if (!$times) $times = 0;
        $times++;
        if ($times > 10) {
            throw new ServiceException('登陆失败次数过多，请稍后再试！');
        }
        Session::set($timesKey, $times);

        $tupleAdminUserAdminLog = Be::newTuple('system_admin_user_log');
        $tupleAdminUserAdminLog->username = $username;
        $tupleAdminUserAdminLog->ip = $ip;
        $tupleAdminUserAdminLog->create_time = time();

        $db = Be::getDb();
        $db->beginTransaction();
        try {

            $tupleAdminUser = Be::newTuple('system_admin_user');

            try {
                $tupleAdminUser->load('username', $username);
            } catch (\Exception $e) {
                throw new ServiceException('管理员（'.$username.'）不存在！');
            }

            $password = $this->encryptPassword($password, $tupleAdminUser->salt);

            if ($tupleAdminUser->password === $password) {
                if ($tupleAdminUser->block == 1) {
                    throw new ServiceException('管理员账号（'.$username.'）已被停用！');
                } else {
                    session::delete($timesKey);

                    $this->makeLogin($tupleAdminUser);

                    $tupleAdminUserAdminLog->success = 1;
                    $tupleAdminUserAdminLog->description = '登陆成功！';

                    $rememberMeToken = null;
                    do {
                        $rememberMeToken = Random::complex(32);
                    } while (Be::newTable('system_admin_user')->where('remember_me_token', $rememberMeToken)->count() > 0);

                    $tupleAdminUser->last_login_time = time();
                    $tupleAdminUser->remember_me_token = $rememberMeToken;
                    $tupleAdminUser->save();

                    cookie::setExpire(time() + 30 * 86400);
                    cookie::set('_adminRememberMe', $rememberMeToken);

                }
            } else {
                throw new ServiceException('密码错误！');
            }

            $db->commit();
            $tupleAdminUserAdminLog->save();
            return $tupleAdminUser;

        } catch (\Exception $e) {
            $db->rollback();

            $tupleAdminUserAdminLog->description = $e->getMessage();
            $tupleAdminUserAdminLog->save();
            throw $e;
        }
    }


    /**
     * 标记用户已成功登录
     *
     * @param Tuple | Object | int $id 用户Row对象 | Object数据 | 用户ID
     * @throws ServiceException
     */
    public function makeLogin($id) {
        $adminUser = null;
        if($id instanceof Tuple) {
            $adminUser = $id->toObject();
        }elseif(is_object($id)) {
            $adminUser = $id;
        }elseif(is_numeric($id)) {
            $tupleAdminUser = Be::newTuple('system_admin_user');
            $tupleAdminUser->load($id);
            $adminUser = $tupleAdminUser->toObject();
        } else {
            throw new ServiceException('参数无法识别！');
        }

        unset($adminUser->password);
        unset($adminUser->salt);
        unset($adminUser->remember_me_token);
        $adminUser->roleIds = Be::newTable('system_admin_user_role')
            ->where('user_id', $adminUser->id)
            ->getArray('role_id');

        Session::set('_adminUser', $adminUser);

        Be::cleanCache('AdminUser:0');
    }

    /**
     * 记住我
     *
     * @return Tuple | false
     * @throws \Exception
     */
    public function rememberMe()
    {
        if (cookie::has('_adminRememberMe')) {
            $adminRememberMe = cookie::get('_adminRememberMe', '');
            if ($adminRememberMe) {
                $tupleAdminUser = Be::newTuple('system_admin_user');
                $tupleAdminUser->load('remember_me_token', $adminRememberMe);
                if ($tupleAdminUser->id && $tupleAdminUser->block == 0) {

                    $this->makeLogin($tupleAdminUser);

                    $db = Be::getDb();
                    $db->beginTransaction();
                    try {

                        $tupleAdminUser->last_login_time = time();
                        $tupleAdminUser->save();

                        $db->commit();
                        return $tupleAdminUser;

                    } catch (\Exception $e) {
                        $db->rollback();
                        throw $e;
                    }
                }
            }
        }
        return false;
    }

    /**
     * 退出
     *
     * @throws ServiceException
     */
    public function logout()
    {
        session::delete('_adminUser');
        cookie::delete('_adminRememberMe');
    }

    /**
     * 初始化管理员头像
     *
     * @param int $id 管理员ID
     * @throws \Exception
     */
    public function initAvatar($id)
    {
        $tupleAdminUser = Be::newTuple('system_admin_user');
        $tupleAdminUser->load($id);
        if (!$tupleAdminUser->id) {
            throw new ServiceException('指定的用户不存在！');
        }
        $this->deleteAvatarFile($tupleAdminUser);
        $tupleAdminUser->save();
    }


    /**
     * 删除头像文件
     *
     * @param $tupleAdminUser
     */
    public function deleteAvatarFile(&$tupleAdminUser) {
        $files = [];

        if ($tupleAdminUser->avatar_s != '') $files[] = Be::getRuntime()->getDataPath() . '/System/AdminUser/Avatar/' . $tupleAdminUser->avatar_s;
        if ($tupleAdminUser->avatar_m != '') $files[] = Be::getRuntime()->getDataPath() . '/System/AdminUser/Avatar/' . $tupleAdminUser->avatar_m;
        if ($tupleAdminUser->avatar_l != '') $files[] = Be::getRuntime()->getDataPath() . '/System/AdminUser/Avatar/' . $tupleAdminUser->avatar_l;

        $tupleAdminUser->avatar_s = '';
        $tupleAdminUser->avatar_m = '';
        $tupleAdminUser->avatar_l = '';

        foreach ($files as $file) {
            if (file_exists($file)) @unlink($file);
        }
    }

    /**
     * 管理员密码加密算法
     *
     * @param string $password 密码
     * @param string $salt 盐值
     * @return string
     */
    public function encryptPassword($password, $salt)
    {
        return sha1(sha1($password) . $salt);
    }


}
