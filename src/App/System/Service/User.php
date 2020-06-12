<?php

namespace Be\App\System\Service;

use Be\System\Db\Tuple;
use Be\System\Exception\ServiceException;
use Be\Util\Random;
use Be\Util\Validator;
use Be\System\Be;
use Be\System\Cookie;
use Be\System\Session;
use PHPMailer\PHPMailer\Exception;

class User extends \Be\System\Service
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

        $timesKey = '_user:login:ip:' . $ip;
        $times = Session::get($timesKey);
        if (!$times) $times = 0;
        $times++;
        if ($times > 10) {
            throw new ServiceException('登陆失败次数过多，请稍后再试！');
        }
        Session::set($timesKey, $times);

        $tupleUserAdminLog = Be::newTuple('system_user_log');
        $tupleUserAdminLog->username = $username;
        $tupleUserAdminLog->ip = $ip;
        $tupleUserAdminLog->create_time = time();

        $db = Be::getDb();
        $db->beginTransaction();
        try {

            $tupleUser = Be::newTuple('system_user');

            try {
                $tupleUser->load('username', $username);
            } catch (\Exception $e) {
                throw new ServiceException('用户账号（'.$username.'）不存在！');
            }

            $password = $this->encryptPassword($password, $tupleUser->salt);

            if ($tupleUser->password === $password) {
                if ($tupleUser->block == 1) {
                    throw new ServiceException('用户账号（'.$username.'）已被停用！');
                } else {
                    session::delete($timesKey);

                    $this->makeLogin($tupleUser);

                    $tupleUserAdminLog->success = 1;
                    $tupleUserAdminLog->description = '登陆成功！';

                    $rememberMeToken = null;
                    do {
                        $rememberMeToken = Random::complex(32);
                    } while (Be::newTable('system_user')->where('remember_me_token', $rememberMeToken)->count() > 0);

                    $tupleUser->last_login_time = time();
                    $tupleUser->remember_me_token = $rememberMeToken;
                    $tupleUser->save();

                    cookie::setExpire(time() + 30 * 86400);
                    cookie::set('_rememberMe', $rememberMeToken);

                }
            } else {
                throw new ServiceException('密码错误！');
            }

            $db->commit();
            $tupleUserAdminLog->save();
            return $tupleUser;

        } catch (\Exception $e) {
            $db->rollback();

            $tupleUserAdminLog->description = $e->getMessage();
            $tupleUserAdminLog->save();
            throw $e;
        }
    }



    /**
     * 标记用户已成功登录
     *
     * @param Tuple | Object | int $userId 用户Row对象 | Object数据 | 用户ID
     * @throws ServiceException
     */
    public function makeLogin($userId) {
        $user = null;
        if($userId instanceof Tuple) {
            $user = $userId->toObject();
        }elseif(is_object($userId)) {
            $user = $userId;
        }elseif(is_numeric($userId)) {
            $tupleUser = Be::newTuple('system_user');
            $tupleUser->load($userId);
            $user = $tupleUser->toObject();
        } else {
            throw new ServiceException('参数无法识别！');
        }

        unset($user->password);
        unset($user->salt);
        unset($user->remember_me_token);
        $user->roleIds = Be::newTable('system_user_role')
            ->where('user_id', $user->id)
            ->getArray('role_id');
        Session::set('_user', $user);

        unset(Be::$cache['User:0']);
    }

    /**
     * 记住我 自动登录
     *
     * @return Tuple | false
     * @throws \Exception
     */
    public function rememberMe()
    {
        if (cookie::has('_rememberMe')) {
            $rememberMe = cookie::get('_rememberMe', '');
            if ($rememberMe) {
                $tupleUser = Be::newTuple('system_user');
                $tupleUser->load('remember_me_token', $rememberMe);

                if ($tupleUser->id > 0 && $tupleUser->block == 0) {

                    $this->makeLogin($tupleUser);

                    $db = Be::getDb();
                    $db->beginTransaction();
                    try {

                        $tupleUser->last_login_time = time();
                        $tupleUser->save();

                        $db->commit();
                        return $tupleUser;

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
     */
    public function logout()
    {
        session::delete('_user');
        cookie::delete('_rememberMe');
    }

    /**
     * 修改用户密码
     *
     * @param int $userId 用户ID
     * @param string $password 当前密码
     * @param string $newPassword 新密码
     * @throws \Exception
     */
    public function changePassword($userId, $password, $newPassword)
    {
        $userId = intval($userId);
        if (!$userId) {
            throw new ServiceException('参数用户ID（userId）缺失！');
        }

        $password = trim($password);
        if (!$password) {
            throw new ServiceException('参数当前密码（password）缺失！');
        }

        $newPassword = trim($newPassword);
        if (!$newPassword) {
            throw new ServiceException('参数新密码（newPassword）缺失！');
        }

        $tupleUser = Be::newTuple('system_user');
        $tupleUser->load($userId);

        if ($this->encryptPassword($password, $tupleUser->salt) != $tupleUser->password) {
            throw new Exception('当前密码错误！');
        }

        $newSalt = Random::complex(32);
        $tupleUser->password = $this->encryptPassword($newPassword, $newSalt);
        $tupleUser->salt = $newSalt;
        $tupleUser->save();
    }


    public function edit($userId, $data = [])
    {
        $tupleUser = Be::newTuple('system_user');
        $tupleUser->load($userId);
        $tupleUser->bind($data);
        $tupleUser->save();
    }

    /**
     * 密码 Hash
     *
     * @param string $password 密码
     * @param string $salt 盐值
     * @return string
     */
    public function encryptPassword($password, $salt)
    {
        return sha1(sha1($password) . $salt);
    }

    /**
     * 删除用户账号
     *
     * @param int $id 用户ID
     * @throws \Exception
     */
    public function delete($id)
    {
        $tupleUser = Be::newTuple('system_user');
        $tupleUser->load($id);

        // 删除头像
        $this->deleteAvatarFile($tupleUser);

        $tupleUser->delete();
    }

    /**
     * 上传头像
     *
     * @param Tuple|mixed $tupleUser
     * @param $avatarFile
     * @throws ServiceException
     */
    public function uploadAvatar($tupleUser, $avatarFile)
    {
        $configSystem = Be::getConfig('System.System');

        if ($avatarFile['error'] == 0) {
            $name = strtolower($avatarFile['name']);
            $type = '';
            $pos = strrpos($name, '.');
            if ($pos !== false) {
                $type = substr($name, $pos + 1);
            }
            if (!in_array($type, $configSystem->allowUploadImageTypes)) {
                throw new ServiceException('您上传的不是合法的图像文件！');
            } else {
                $libImage = Be::getLib('image');
                $libImage->open($avatarFile['tmp_name']);
                if (!$libImage->isImage()) {
                    throw new ServiceException('您上传的不是合法的图像文件！');
                } else {
                    $configUser = Be::getConfig('System.User');

                    $avatarDir = Be::getRuntime()->getDataPath() . '/System/User/Avatar/';
                    if (!file_exists($avatarDir)) {
                        mkdir($avatarDir, 0777, true);
                    }

                    // 删除旧头像
                    $this->deleteAvatarFile($tupleUser);

                    $t = date('YmdHis');

                    $imageType = $libImage->getType();

                    // 按配置文件里的尺寸大小生成新头像
                    $libImage->resize($configUser->avatarLW, $configUser->avatarLH, 'north');
                    $libImage->save($avatarDir . $tupleUser->id . '_' . $t . '_l.' . $imageType);
                    $tupleUser->avatar_l = $tupleUser->id . '_' . $t . '_l.' . $imageType;

                    $libImage->resize($configUser->avatarMW, $configUser->avatarMH, 'north');
                    $libImage->save($avatarDir . $tupleUser->id . '_' . $t . '_m.' . $imageType);
                    $tupleUser->avatar_m = $tupleUser->id . '_' . $t . '_m.' . $imageType;

                    $libImage->resize($configUser->avatarSW, $configUser->avatarSH, 'north');
                    $libImage->save($avatarDir . $tupleUser->id . '_' . $t . '_s.' . $imageType);
                    $tupleUser->avatar_s = $tupleUser->id . '_' . $t . '_s.' . $imageType;

                    $tupleUser->save();
                }
            }

            @unlink($avatarFile['tmp_name']);
        } else {
            $uploadErrors = array(
                '1' => '您上传的文件过大！',
                '2' => '您上传的文件过大！',
                '3' => '文件只有部分被上传！',
                '4' => '没有文件被上传！',
                '5' => '上传的文件大小为 0！'
            );
            $error = null;
            if (array_key_exists($avatarFile['error'], $uploadErrors)) {
                $error = $uploadErrors[$avatarFile['error']];
            } else {
                $error = '错误代码：' . $avatarFile['error'];
            }

            throw new ServiceException('上传失败' . '(' . $error . ')');
        }
    }

    /**
     * 初始化用户头像
     *
     * @param int $userId 用户ID
     * @throws \Exception
     */
    public function initAvatar($userId)
    {
        $tupleUser = Be::newTuple('system_user');
        $tupleUser->load($userId);
        if (!$tupleUser->id) {
            throw new ServiceException('指定的用户不存在！');
        }

        $this->deleteAvatarFile($tupleUser);

        if (!$tupleUser->save()) {
            throw new \Exception($tupleUser->getError());
        }
    }



    /**
     * 删除头像文件
     *
     * @param $tupleUser
     */
    public function deleteAvatarFile(&$tupleUser) {
        $files = [];

        if ($tupleUser->avatar_s != '') $files[] = Be::getRuntime()->getDataPath() . '/System/User/Avatar/' . $tupleUser->avatar_s;
        if ($tupleUser->avatar_m != '') $files[] = Be::getRuntime()->getDataPath() . '/System/User/Avatar/' . $tupleUser->avatar_m;
        if ($tupleUser->avatar_l != '') $files[] = Be::getRuntime()->getDataPath() . '/System/User/Avatar/' . $tupleUser->avatar_l;

        $tupleUser->avatar_s = '';
        $tupleUser->avatar_m = '';
        $tupleUser->avatar_l = '';

        foreach ($files as $file) {
            if (file_exists($file)) @unlink($file);
        }
    }

    /**
     * 检测用户名是否可用
     *
     * @param $username
     * @param int $userId
     * @return bool
     */
    public function isUsernameAvailable($username, $userId = 0)
    {
        $table = Be::newTable('system_user');
        if ($userId > 0) {
            $table->where('id', '!=', $userId);
        }
        $table->where('username', $username);
        return $table->count() == 0;
    }

    /**
     * 检测邮箱是否可用
     *
     * @param $email
     * @param int $userId
     * @return bool
     */
    public function isEmailAvailable($email, $userId = 0)
    {
        $table = Be::newTable('system_user');
        if ($userId > 0) {
            $table->where('id', '!=', $userId);
        }
        $table->where('email', $email);
        return $table->count() == 0;
    }

}
