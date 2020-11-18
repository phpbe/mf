<?php

namespace Be\App\System\Service;

use Be\System\Db\Tuple;
use Be\System\Exception\ServiceException;
use Be\System\Request;
use Be\Util\Net\FileUpload;
use Be\Util\Random;
use Be\Util\Validator;
use Be\System\Be;
use Be\System\Cookie;
use Be\System\Session;
use PHPMailer\PHPMailer\Exception;

class User
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

        $tupleUserLoginLog = Be::newTuple('system_user_login_log');
        $tupleUserLoginLog->username = $username;
        $tupleUserLoginLog->ip = $ip;
        $tupleUserLoginLog->create_time = date('Y-m-d H:i:s');

        $db = Be::getDb();
        $db->beginTransaction();
        try {

            $tupleUser = Be::newTuple('system_user');

            try {
                $tupleUser->loadBy('username', $username);
            } catch (\Exception $e) {
                throw new ServiceException('用户账号（'.$username.'）不存在！');
            }

            $password = $this->encryptPassword($password, $tupleUser->salt);

            if ($tupleUser->password === $password) {
                if ($tupleUser->is_delete == 1) {
                    throw new ServiceException('用户账号（'.$username.'）不可用！');
                } elseif ($tupleUser->is_enable == 0) {
                    throw new ServiceException('用户账号（'.$username.'）已被禁用！');
                } else {
                    session::delete($timesKey);

                    $this->makeLogin($tupleUser);

                    $tupleUserLoginLog->success = 1;
                    $tupleUserLoginLog->description = '登陆成功！';

                    $rememberMeToken = null;
                    do {
                        $rememberMeToken = Random::complex(32);
                    } while (Be::newTable('system_user')->where('remember_me_token', $rememberMeToken)->count() > 0);

                    $tupleUser->last_login_time = date('Y-m-d H:i:s');
                    $tupleUser->last_login_ip = Request::ip();
                    $tupleUser->remember_me_token = $rememberMeToken;
                    $tupleUser->save();

                    cookie::setExpire(time() + 30 * 86400);
                    cookie::set('_rememberMe', $rememberMeToken);

                }
            } else {
                throw new ServiceException('密码错误！');
            }

            $db->commit();
            $tupleUserLoginLog->save();
            return $tupleUser;

        } catch (\Exception $e) {
            $db->rollback();

            $tupleUserLoginLog->description = $e->getMessage();
            $tupleUserLoginLog->save();
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
                try {
                    $tupleUser->loadBy('remember_me_token', $rememberMe);
                } catch (\Exception $e) {

                }

                if ($tupleUser->id > 0 && $tupleUser->is_enable == 1 && $tupleUser->is_delete == 0) {
                    $this->makeLogin($tupleUser);
                    $db = Be::getDb();
                    $db->beginTransaction();
                    try {

                        $tupleUser->last_login_time = date('Y-m-d H:i:s');
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

}
