<?php

namespace Be\Mf\User;

use Be\Mf\Be;

class RoleFactory
{

    private static $cache = [];

    /**
     * 获取指定的一个角色信息（单例）
     *
     * @param int $roleId 角色ID
     * @return Role
     */
    public static function getInstance($roleId)
    {
        if (isset(self::$cache[$roleId])) return self::$cache[$roleId];

        $path = Be::getRuntime()->getCachePath() . '/Role/Role' . $roleId . '.php';
        if (!file_exists($path)) {
            $service = Be::getService('System.Role');
            $service->updateRole($roleId);
            include_once $path;
        }

        $class = 'Be\\Mf\\Cache\\Role\\Role' . $roleId;
        self::$cache[$roleId] = new $class();
        return self::$cache[$roleId];
    }

}

