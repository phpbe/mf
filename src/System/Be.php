<?php

namespace Be\System;

use Be\System\Exception\RuntimeException;

/**
 *  BE系统资源工厂
 * @package System
 *
 */
abstract class Be
{

    public static $cache = []; // 缓存资源实例

    /**
     * @var Runtime
     */
    private static $runtime = null; // 系统运行时


    /**
     * 获取数据库对象（单例）
     *
     * @param string $db 数据库名
     * @return \Be\System\Db\Driver
     * @throws RuntimeException
     */
    public static function getDb($db = 'master')
    {
        $key = 'Db:' . $db;
        if (isset(self::$cache[$key])) return self::$cache[$key];
        self::$cache[$key] = self::newDb($db);
        return self::$cache[$key];
    }

    /**
     * 获取有效期的数据库对象（单例）
     * 如果实例已创建时间超过了有效期，则创建新实例
     *
     * @param string $db 数据库名
     * @param int $expire 有效时间(单位：秒)
     * @return \Be\System\Db\Driver
     * @throws RuntimeException
     */
    public static function getExpireDb($db = 'master', $expire = 600)
    {
        $key = 'ExpireDb:' . $db;
        if (isset(self::$cache[$key]['expire']) && self::$cache[$key]['expire'] > time()) {
            return self::$cache[$key]['instance'];
        }

        self::$cache[$key] = [
            'expire' => time() + $expire,
            'instance' => self::newDb($db)
        ];
        return self::$cache[$key]['instance'];
    }

    /**
     * 新创建一个数据库对象
     *
     * @param string $db 数据库名
     * @return \Be\System\Db\Driver
     * @throws RuntimeException
     */
    public static function newDb($db = 'master')
    {
        $config = self::getConfig('System.Db');
        if (!isset($config->$db)) {
            throw new \RuntimeException('数据库配置项（' . $db . '）不存在！');
        }

        $config = $config->$db;

        $class = 'Be\\System\\Db\\Driver\\' . $config['driver'] . 'Impl';
        if (!class_exists($class)) throw new \RuntimeException('数据库配置项（' . $db . '）指定的数据库驱动' . $config['driver'] . '不支持！');

        return new $class($config);
    }

    /**
     * 获取Redis对象（单例）
     *
     * @param string $redis Redis名
     * @return \Be\System\Redis\Driver
     * @throws RuntimeException
     */
    public static function getRedis($redis = 'master')
    {
        $key = 'Redis:' . $redis;
        if (isset(self::$cache[$key])) return self::$cache[$key];
        self::$cache[$key] = self::newRedis($redis);
        return self::$cache[$key];
    }

    /**
     * 新创建一个Redis对象
     *
     * @param string $redis Redis名
     * @return \Be\System\Redis\Driver
     * @throws RuntimeException
     */
    public static function newRedis($redis = 'master')
    {
        $config = self::getConfig('System.Redis');
        if (!isset($config->$redis)) {
            throw new RuntimeException('Redis配置项（' . $redis . '）不存在！');
        }
        return new \Be\System\Redis\Driver($config->$redis);
    }

    /**
     * 获取MongoDB对象（单例）
     *
     * @param string $mongoDB MongoDB名
     * @return \Be\System\MongoDB\Driver
     * @throws RuntimeException
     */
    public static function getMongoDB($mongoDB = 'master')
    {
        $key = 'MongoDB:' . $mongoDB;
        if (isset(self::$cache[$key])) return self::$cache[$key];
        self::$cache[$key] = self::newMongoDB($mongoDB);
        return self::$cache[$key];
    }

    /**
     * 新创建一个MongoDB对象
     *
     * @param string $mongoDB MongoDB名
     * @return \Be\System\MongoDB\Driver
     * @throws RuntimeException
     */
    public static function newMongoDB($mongoDB = 'master')
    {
        $config = self::getConfig('System.MongoDB');
        if (!isset($config->$mongoDB)) {
            throw new RuntimeException('MongoDB配置项（' . $mongoDB . '）不存在！');
        }
        return new \Be\System\MongoDB\Driver($config->$mongoDB);
    }

    /**
     * 获取指定的库（单例）
     *
     * @param string $lib 库名，可指定命名空间，调用第三方库
     * @return Lib | mixed
     * @throws RuntimeException
     */
    public static function getLib($lib)
    {
        $key = 'Lib:' . $lib;
        if (isset(self::$cache[$key])) return self::$cache[$key];
        self::$cache[$key] = self::newLib($lib);
        return self::$cache[$key];
    }

    /**
     * 新创建一个指定的库
     *
     * @param string $lib 库名，可指定命名空间，调用第三方库
     * @return Lib | mixed
     * @throws RuntimeException
     */
    public static function newLib($lib)
    {
        $class = null;
        if (strpos($lib, '\\') === false) {
            $class = 'Be\\Lib\\' . $lib . '\\' . $lib;
        } else {
            $class = $lib;
        }
        if (!class_exists($class)) throw new RuntimeException('库 ' . $class . ' 不存在！');

        return new $class();
    }

    /**
     * 获取指定的一个扩展（单例）
     *
     * @param string $plugin 扩展名
     * @return mixed
     * @throws RuntimeException
     */
    public static function getPlugin($plugin)
    {
        $key = 'plugin:' . $plugin;
        if (isset(self::$cache[$key])) return self::$cache[$key];
        self::$cache[$key] = self::newPlugin($plugin);
        return self::$cache[$key];
    }

    /**
     * 新创建一个指定的扩展
     *
     * @param string $plugin 扩展名
     * @return mixed
     * @throws RuntimeException
     */
    public static function newPlugin($plugin)
    {
        $class = 'Be\\Plugin\\' . $plugin . '\\' . $plugin;
        if (!class_exists($class)) {
            throw new RuntimeException('扩展 ' . $plugin . ' 不存在！');
        }

        return new $class();
    }

    /**
     * 获取指定的配置文件（单例）
     *
     * @param string $name 配置文件名
     * @return mixed
     * @throws RuntimeException
     */
    public static function getConfig($name)
    {
        $key = 'Config:' . $name;
        if (isset(self::$cache[$key])) return self::$cache[$key];
        self::$cache[$key] = self::newConfig($name);
        return self::$cache[$key];
    }

    /**
     * 新创建一个指定的配置文件
     *
     * @param string $name 配置文件名
     * @return mixed
     * @throws RuntimeException
     */
    public static function newConfig($name)
    {
        $parts = explode('.', $name);
        $appName = $parts[0];
        $configName = $parts[1];

        $class = 'Be\\Data\\System\\Config\\' . $appName . '\\' . $configName;
        if (class_exists($class)) {
            return new $class();
        }

        $class = 'Be\\App\\' . $appName . '\\Config\\' . $configName;
        if (class_exists($class)) {
            return new $class();
        }

        throw new RuntimeException('配置文件 ' . $name . ' 不存在！');
    }

    /**
     * 获取指定的一个服务（单例）
     *
     * @param string $name 服务名
     * @return Service | mixed
     */
    public static function getService($name)
    {
        $key = 'Service:' . $name;
        if (isset(self::$cache[$key])) return self::$cache[$key];
        self::$cache[$key] = self::newService($name);
        return self::$cache[$key];
    }

    /**
     * 新创建一个服务
     *
     * @param string $name 服务名
     * @return Service | mixed
     */
    public static function newService($name)
    {
        $parts = explode('.', $name);
        $app = array_shift($parts);
        $class = 'Be\\App\\' . $app . '\\Service\\' . implode('\\', $parts);
        return new $class();
    }

    /**
     * 获取指定的一个数据库行记灵对象（单例）
     *
     * @param string $name 数据库行记灵对象名
     * @param string $db 库名
     * @return \Be\System\Db\Tuple | mixed
     */
    public static function getTuple($name, $db = 'master')
    {
        $key = 'Tuple:' . $db . ':' . $name;
        if (isset(self::$cache[$key])) return self::$cache[$key];
        self::$cache[$key] = self::newTuple($name);
        return self::$cache[$key];
    }

    /**
     * 新创建一个数据库行记灵对象
     *
     * @param string $name 数据库行记灵对象名
     * @param string $db 库名
     * @return \Be\System\Db\Tuple | mixed
     */
    public static function newTuple($name, $db = 'master')
    {
        $path = self::$runtime->getCachePath() . '/System/Tuple/'.$db.'/'.$name.'.php';
        $configSystem = self::getConfig('System.System');
        if ($configSystem->developer || !file_exists($path)) {
            $service = self::getService('System.Db');
            $service->updateTuple($name, $db);
            include_once $path;
        }

        $class = 'Be\\Cache\\System\\Tuple\\' . $db . '\\' . $name;
        return (new $class());
    }

    /**
     * 获取指定的一个数据库表对象（单例）
     *
     * @param string $name 表名
     * @param string $db 库名
     * @return \Be\System\Db\Table
     */
    public static function getTable($name, $db = 'master')
    {
        $key = 'Table:' . $db . ':'  . $name;
        if (isset(self::$cache[$key])) return self::$cache[$key];
        self::$cache[$key] = self::newTable($name, $db);
        return self::$cache[$key];
    }

    /**
     * 新创建一个数据库表对象
     *
     * @param string $name 表名
     * @param string $db 库名
     * @return \Be\System\Db\Table
     */
    public static function newTable($name, $db = 'master')
    {
        $path = self::$runtime->getCachePath() . '/System/Table/'.$db.'/'.$name.'.php';
        $configSystem = self::getConfig('System.System');
        if ($configSystem->developer || !file_exists($path)) {
            $service = self::getService('System.Db');
            $service->updateTable($name, $db);
            include_once $path;
        }

        $class = 'Be\\Cache\\System\\Table\\' . $db . '\\' . $name;
        return (new $class());
    }

    /**
     * 获取指定的一个数据库表属性（单例）
     *
     * @param string $name 表名
     * @param string $db 库名
     * @return \Be\System\Db\TableProperty
     */
    public static function getTableProperty($name, $db = 'master')
    {
        $key = 'TableProperty:' . $db . ':'  . $name;
        if (isset(self::$cache[$key])) return self::$cache[$key];

        $path = self::$runtime->getCachePath() . '/System/TableProperty/'.$db.'/'.$name.'.php';
        $configSystem = self::getConfig('System.System');
        if ($configSystem->developer || !file_exists($path)) {
            $service = self::getService('System.Db');
            $service->updateTableProperty($name, $db);
            include_once $path;
        }

        $class = 'Be\\Cache\\System\\TableProperty\\' . $db . '\\' . $name;
        self::$cache[$key] = new $class();
        return self::$cache[$key];
    }

    /**
     * 获取指定的一个菜单（单例）
     *
     * @return Menu
     */
    public static function getMenu()
    {
        $key = 'Menu' ;
        if (isset(self::$cache[$key])) return self::$cache[$key];

        $path = self::$runtime->getCachePath() . '/System/Menu.php';
        $configSystem = self::getConfig('System.System');
        if ($configSystem->developer || !file_exists($path)) {
            $service = self::getService('System.Menu');
            $service->update();
            include_once $path;
        }

        $class = 'Be\\Cache\\System\\Menu';
        self::$cache[$key] = new $class();
        return self::$cache[$key];
    }

    /**
     * 获取指定的一个角色信息（单例）
     *
     * @param int $roleId 角色ID
     * @return Role
     */
    public static function getRole($roleId)
    {
        $key = 'Role:' . $roleId;
        if (isset(self::$cache[$key])) return self::$cache[$key];

        $path = self::$runtime->getCachePath() . '/System/Role/Role' . $roleId . '.php';
        $configSystem = self::getConfig('System.System');
        if ($configSystem->developer || !file_exists($path)) {
            $service = self::getService('System.Role');
            $service->updateRole($roleId);
            include_once $path;
        }

        $class = 'Be\\Cache\\System\\Role\\Role' . $roleId;
        self::$cache[$key] = new $class();
        return self::$cache[$key];
    }

    /**
     * 获取一个属性（单例）
     *
     * @param string $name 名称
     * @return Property
     * @throws RuntimeException
     */
    public static function getProperty($name)
    {
        $key = 'Property:' . $name;
        if (isset(self::$cache[$key])) return self::$cache[$key];

        $parts = explode('.', $name);
        $class = 'Be\\' . implode('\\', $parts) . '\\Property';
        if (!class_exists($class)) throw new RuntimeException('属性 ' . $name . ' 不存在！');
        $instance = new $class();

        self::$cache[$key] = $instance;
        return self::$cache[$key];
    }

    /**
     * 获取指定的一个模板（单例）
     *
     * @param string $template 模板名
     * @param string $theme 主题名
     * @return Template
     * @throws RuntimeException
     */
    public static function getTemplate($template, $theme = null)
    {
        $parts = explode('.', $template);
        $type = array_shift($parts);
        $name = array_shift($parts);

        $configSystem = self::getConfig('System.System');

        if ($theme === null) {
            $property = Be::getProperty($type . '.' . $name);
            if (isset($property->theme)) {
                $theme = $property->theme;
            } else {
                $theme = $configSystem->theme;
            }
        }

        $key = 'Template:' . $theme.':' . $template;
        if (isset(self::$cache[$key])) return self::$cache[$key];

        $path = self::$runtime->getCachePath() . '/System/Template/' . $theme . '/' . $type . '/' . $name . '/' . implode('/', $parts) . '.php';
        if ($configSystem->developer || !file_exists($path)) {
            $service = self::getService('System.Template');
            $service->update($template, $theme);
        }

        $class = 'Be\\Cache\\System\\Template\\' . $theme . '\\' . $type . '\\' . $name . '\\' . implode('\\', $parts);
        self::$cache[$key] = new $class();
        return self::$cache[$key];
    }

    /**
     * 获取一个用户 实例（单例）
     *
     * @param int $id 用户编号
     * @return User | mixed
     */
    public static function getUser($id = 0)
    {
        $key = 'User:' . $id;
        if (isset(self::$cache[$key])) return self::$cache[$key];

        $user = null;
        if ($id == 0) {
            $user = Session::get('_user');
        } else {
            $user = self::getTuple('system_user')->load($id)->toObject();
            if ($user) {
                unset($user->password, $user->salt, $user->remember_me_token);
            }
        }

        self::$cache[$key] = new User($user);
        return self::$cache[$key];
    }

    public static function getRuntime()
    {
        if (self::$runtime == null) {
            self::$runtime = new Runtime();
        }
        return self::$runtime;
    }

}
