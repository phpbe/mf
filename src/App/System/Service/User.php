<?php

namespace Be\Mf\App\System\Service;

use Be\F\Db\Tuple;
use Be\F\Exception\ServiceException;
use Be\F\Util\Random;
use Be\Mf\Be;

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

        $request = Be::getRequest();
        $response = Be::getResponse();
        $session = Be::getSession();

        $timesKey = '_user:login:ip:' . $ip;
        $times = $session->get($timesKey);
        if (!$times) $times = 0;
        $times++;
        if ($times > 10) {
            throw new ServiceException('登陆失败次数过多，请稍后再试！');
        }
        $session->set($timesKey, $times);

        $tupleUserLoginLog = Be::newTuple('system_user_login_log');
        $tupleUserLoginLog->username = $username;
        $tupleUserLoginLog->ip = $ip;
        $tupleUserLoginLog->create_time = date('Y-m-d H:i:s');

        $db = Be::getDb();
        $db->beginTransaction();
        try {
            $tupleUser = Be::newTuple('system_user');

            $configUser = Be::getConfig('System.User');
            if ($configUser->ldap) {

                $conn = null;
                try {
                    $conn = ldap_connect($configUser->ldap_host);
                } catch (\Throwable $e) {
                    throw new ServiceException('无法连接到LDAP服务器（' . $configUser->ldap_host . '）！');
                }

                ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);

                $bind = null;
                try {
                    if ($configUser->ldap_pattern) {
                        $pattern = str_replace('{username}', $username, $configUser->ldap_pattern);
                        $bind = ldap_bind($conn, $pattern, $password);
                    } else {
                        $bind = ldap_bind($conn, $username, $password);
                    }
                } catch (\Throwable $e) {
                    $ldapErr = ldap_error($conn);
                    ldap_close($conn);
                    throw new ServiceException('LDAP登录失败' . ($ldapErr ? ('（' . $ldapErr . '）') : '') . '！');
                }

                if (!$bind) {
                    ldap_close($conn);
                    throw new ServiceException('用户账号和密码不匹配！');
                }

                ldap_close($conn);

                try {
                    $tupleUser->loadBy('username', $username);
                } catch (\Exception $e) {
                    $tupleUser->username = $username;
                    $tupleUser->salt = Random::complex(32);
                    $tupleUser->create_time = date('Y-m-d H:i:s');
                }

                $tupleUser->password = $this->encryptPassword($password, $tupleUser->salt);
                $tupleUser->last_login_time = $tupleUser->this_login_time;
                $tupleUser->this_login_time = date('Y-m-d H:i:s');
                $tupleUser->last_login_ip = $tupleUser->this_login_ip;
                $tupleUser->this_login_ip = $ip;
                $tupleUser->update_time = date('Y-m-d H:i:s');
                $tupleUser->save();

            } else {

                try {
                    $tupleUser->loadBy('username', $username);
                } catch (\Exception $e) {
                    throw new ServiceException('用户账号（' . $username . '）不存在！');
                }

                if ($tupleUser->password === $this->encryptPassword($password, $tupleUser->salt)) {
                    if ($tupleUser->is_delete == 1) {
                        throw new ServiceException('用户账号（' . $username . '）不可用！');
                    } elseif ($tupleUser->is_enable == 0) {
                        throw new ServiceException('用户账号（' . $username . '）已被禁用！');
                    } else {
                        $tupleUser->last_login_time = $tupleUser->this_login_time;
                        $tupleUser->this_login_time = date('Y-m-d H:i:s');
                        $tupleUser->last_login_ip = $tupleUser->this_login_ip;
                        $tupleUser->this_login_ip = $ip;
                        $tupleUser->update_time = date('Y-m-d H:i:s');
                        $tupleUser->save();
                    }
                } else {
                    throw new ServiceException('密码错误！');
                }
            }

            $this->makeLogin($tupleUser);

            $rememberMe = $username . '|' . base64_encode($this->rc4($password, $tupleUser->salt));

            $response->cookie('_rememberMe', $rememberMe, time() + 30 * 86400);

            $tupleUserLoginLog->success = 1;
            $tupleUserLoginLog->description = '登陆成功！';

            $session->delete($timesKey);

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
    public function makeLogin($userId)
    {
        $user = null;
        if ($userId instanceof Tuple) {
            $user = $userId->toObject();
        } elseif (is_object($userId)) {
            $user = $userId;
        } elseif (is_numeric($userId)) {
            $tupleUser = Be::newTuple('system_user');
            $tupleUser->load($userId);
            $user = $tupleUser->toObject();
        } else {
            throw new ServiceException('参数无法识别！');
        }

        unset($user->password);
        unset($user->salt);
        unset($user->remember_me_token);

        Be::getSession()->set('_user', $user);
        unset(Be::$cache['User:0']);
    }

    /**
     * 记住我 自动登录
     *
     * @throws \Exception
     */
    public function rememberMe()
    {
        $request = Be::getRequest();
        $rememberMe = $request->cookie('_rememberMe', null);
        if ($rememberMe) {
            $rememberMe = explode('|', $rememberMe);
            if (count($rememberMe) != 2) return;

            $username = $rememberMe[0];

            $tupleUser = Be::newTuple('system_user');
            try {
                $tupleUser->loadBy('username', $username);

                $password = base64_decode($rememberMe[1]);
                $password = $this->rc4($password, $tupleUser->salt);

                $this->login($username, $password, $request->getIp());
            } catch (\Exception $e) {
                return;
            }
        }
    }

    /**
     * 退出
     *
     */
    public function logout()
    {
        Be::getSession()->destroy();
        Be::getResponse()->cookie('_rememberMe', '', -1);
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


    public function rc4($txt, $pwd)
    {
        $result = '';
        $kL = strlen($pwd);
        $tL = strlen($txt);
        $level = 256;
        $key = [];
        $box = [];

        for ($i = 0; $i < $level; ++$i) {
            $key[$i] = ord($pwd[$i % $kL]);
            $box[$i] = $i;
        }

        for ($j = $i = 0; $i < $level; ++$i) {
            $j = ($j + $box[$i] + $key[$i]) % $level;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        for ($a = $j = $i = 0; $i < $tL; ++$i) {
            $a = ($a + 1) % $level;
            $j = ($j + $box[$a]) % $level;

            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;

            $k = $box[($box[$a] + $box[$j]) % $level];
            $result .= chr(ord($txt[$i]) ^ $k);
        }

        return $result;
    }


}
