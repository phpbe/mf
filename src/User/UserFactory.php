<?php

namespace Be\Mf\User;

use Be\Mf\Be;

class UserFactory
{

    private static $cache = [];

    /**
     * 获取一个用户 实例（单例）
     *
     * @return User | mixed
     */
    public static function getInstance()
    {
        $cid = \Swoole\Coroutine::getuid();
        if (isset(self::$cache[$cid])) {
            return self::$cache[$cid];
        }

        $user = Be::getSession()->get('_user');
        self::$cache[$cid] = new User($user);
        return self::$cache[$cid];
    }

    /**
     * 回收资源
     */
    public static function recycle()
    {
        $cid = \Swoole\Coroutine::getuid();
        unset(self::$cache[$cid]);
    }

}

