<?php

namespace Be\Mf;

use Be\Framework\Cache\CacheFactory;
use Be\Framework\Db\DbFactory;
use Be\Framework\Db\TableFactory;
use Be\Framework\Db\TablePropertyFactory;
use Be\Framework\Db\TupleFactory;
use Be\Framework\Lib\LibFactory;
use Be\Framework\Plugin\PluginFactory;
use Be\Framework\Runtime\RuntimeException;
use Be\Framework\Session\SessionFactory;
use Be\Framework\Template\TemplateFactory;

/**
 *  BE系统资源工厂
 * @package System
 *
 */
abstract class Be extends \Be\Framework\Be
{


    /**
     * 获取SESSION
     *
     * @return \Be\Framework\Session\Driver
     */
    public static function getSession()
    {
        return SessionFactory::getInstance();
    }

    /**
     * 获取Cache
     *
     * @return \Be\Framework\Cache\Driver
     */
    public static function getCache()
    {
        return CacheFactory::getInstance();
    }

    /**
     * 获取数据库对象（单例）
     *
     * @param string $name 数据库名
     * @return \Be\Framework\Db\Driver
     * @throws RuntimeException
     */
    public static function getDb($name = 'master')
    {
        return DbFactory::getInstance($name);
    }

    /**
     * 获取有效期的数据库对象（单例）
     * 如果实例已创建时间超过了有效期，则创建新实例
     *
     * @param string $name 数据库名
     * @param int $expire 有效时间(单位：秒)
     * @return \Be\Framework\Db\Driver
     * @throws RuntimeException
     */
    public static function getExpireDb($name = 'master', $expire = 600)
    {
        return DbFactory::getExpireInstance($name, $expire);
    }

    /**
     * 新创建一个数据库对象
     *
     * @param string $name 数据库名
     * @return \Be\Framework\Db\Driver
     * @throws RuntimeException
     */
    public static function newDb($name = 'master')
    {
        return DbFactory::newInstance($name);
    }

    /**
     * 获取指定的一个数据库行记灵对象（单例）
     *
     * @param string $name 数据库行记灵对象名
     * @param string $db 库名
     * @return \Be\Framework\Db\Tuple | mixed
     */
    public static function getTuple($name, $db = 'master')
    {
        return TupleFactory::getInstance($name, $db);
    }

    /**
     * 新创建一个数据库行记灵对象
     *
     * @param string $name 数据库行记灵对象名
     * @param string $db 库名
     * @return \Be\Framework\Db\Tuple | mixed
     */
    public static function newTuple($name, $db = 'master')
    {
        return TupleFactory::newInstance($name, $db);
    }

    /**
     * 获取指定的一个数据库表对象（单例）
     *
     * @param string $name 表名
     * @param string $db 库名
     * @return \Be\Framework\Db\Table
     */
    public static function getTable($name, $db = 'master')
    {
        return TableFactory::getInstance($name, $db);
    }

    /**
     * 新创建一个数据库表对象
     *
     * @param string $name 表名
     * @param string $db 库名
     * @return \Be\Framework\Db\Table
     */
    public static function newTable($name, $db = 'master')
    {
        return TableFactory::newInstance($name, $db);
    }

    /**
     * 获取指定的一个数据库表属性（单例）
     *
     * @param string $name 表名
     * @param string $db 库名
     * @return \Be\Framework\Db\TableProperty
     */
    public static function getTableProperty($name, $db = 'master')
    {
        return TablePropertyFactory::getInstance($name, $db);
    }

    /**
     * 获取指定的库（单例）
     *
     * @param string $name 库名，可指定命名空间，调用第三方库
     * @return mixed
     * @throws RuntimeException
     */
    public static function getLib($name)
    {
        return LibFactory::getInstance($name);
    }

    /**
     * 新创建一个指定的库
     *
     * @param string $name 库名，可指定命名空间，调用第三方库
     * @return mixed
     * @throws RuntimeException
     */
    public static function newLib($name)
    {
        return LibFactory::newInstance($name);
    }

    /**
     * 获取指定的一个扩展（单例）
     *
     * @param string $name 扩展名
     * @return mixed
     * @throws RuntimeException
     */
    public static function getPlugin($name)
    {
        return PluginFactory::getInstance($name);
    }

    /**
     * 新创建一个指定的扩展
     *
     * @param string $name 扩展名
     * @return mixed
     * @throws RuntimeException
     */
    public static function newPlugin($name)
    {
        return PluginFactory::newInstance($name);
    }

    /**
     * 获取指定的一个模板（单例）
     *
     * @param string $template 模板名
     * @param string $theme 主题名
     * @return \Be\Framework\Template\Driver
     * @throws RuntimeException
     */
    public static function getTemplate($template, $theme = null)
    {
        return TemplateFactory::getInstance($template, $theme);
    }


    /**
     * 获取指定的一个菜单（单例）
     *
     * @return \Be\Framework\Menu\Driver
     */
    public static function getMenu()
    {
        if (isset(self::$cache['Menu'])) return self::$cache['Menu'];

        $path = self::getRuntime()->getCachePath() . '/Framework/Menu.php';
        $configSystem = self::getConfig('System.System');
        if ($configSystem->developer || !file_exists($path)) {
            $service = self::getService('System.Menu');
            $service->update();
            include_once $path;
        }

        $class = 'Be\\Cache\\Framework\\Menu';
        self::$cache['Menu'] = new $class();
        return self::$cache['Menu'];
    }


    /**
     * 获取指定的一个角色信息（单例）
     *
     * @param int $roleId 角色ID
     * @return Role
     */
    public static function getRole($roleId)
    {
        if (isset(self::$cache['Role'][$roleId])) return self::$cache['Role'][$roleId];

        $path = self::getRuntime()->getCachePath() . '/System/Role/Role' . $roleId . '.php';
        $configSystem = self::getConfig('System.System');
        if ($configSystem->developer || !file_exists($path)) {
            $service = self::getService('System.Role');
            $service->updateRole($roleId);
            include_once $path;
        }

        $class = 'Be\\Cache\\Framework\\Role\\Role' . $roleId;
        self::$cache['Role'][$roleId] = new $class();
        return self::$cache['Role'][$roleId];
    }

    /**
     * 获取一个用户 实例（单例）
     *
     * @param int $id 用户编号
     * @return User | mixed
     */
    public static function getUser($id = 0)
    {
        if (isset(self::$cache['User'][$id])) return self::$cache['User'][$id];

        $user = null;
        if ($id == 0) {
            $user = self::getSession()->get('_user');
        } else {
            $user = self::getTuple('system_user')->load($id)->toObject();
            if ($user) {
                unset($user->password, $user->salt, $user->remember_me_token);
            }
        }

        self::$cache['User'][$id] = new User($user);
        return self::$cache['User'][$id];
    }

}
