<?php

namespace Be\System;


/**
 *  BE系统资源工厂
 * @package System
 *
 */
abstract class Be
{

    private static $cache = []; // 缓存资源实例

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
     * 获取指定的UI（单例）
     *
     * @param string $ui UI名，可指定命名空间，调用第三方UI扩展
     * @return Ui | mixed
     * @throws RuntimeException
     */
    public static function getUi($ui)
    {
        $key = 'Ui:' . $ui;
        if (isset(self::$cache[$key])) return self::$cache[$key];
        self::$cache[$key] = self::newUi($ui);
        return self::$cache[$key];
    }

    /**
     * 新创建一个指定的UI
     *
     * @param string $ui UI名，可指定命名空间，调用第三方UI扩展
     * @return Ui | mixed
     * @throws RuntimeException
     */
    public static function newUi($ui)
    {
        $class = null;
        if (strpos($ui, '\\') === false) {
            $class = 'Ui\\' . $ui . '\\' . $ui;
        } else {
            $class = $ui;
        }
        if (!class_exists($class)) throw new RuntimeException('UI ' . $class . ' 不存在！');

        return new $class();
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
        $names = explode('.', $name);
        $app = $names[0];
        $name = $names[1];

        $class = 'Be\\Data\\System\\Config\\' . $app . '\\' . $name;
        if (class_exists($class)) {
            return new $class();
        }

        $class = 'Be\\App\\' . $app . '\\Config\\' . $name;
        if (class_exists($class)) {
            return new $class();
        }

        throw new RuntimeException('配置文件 ' . $app . '\\' . $name . ' 不存在！');
    }

    /**
     * 获取指定的一个服务（单例）
     *
     * @param string $name 服务名
     * @return Service | mixed
     * @throws RuntimeException
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
     * @throws RuntimeException
     */
    public static function newService($name)
    {
        $parts = explode('.', $name);
        $app = array_shift($parts);

        $class = 'Be\\App\\' . $app . '\\Service\\' . implode('\\', $parts);
        if (!class_exists($class)) throw new RuntimeException('服务 ' . $name . ' 不存在！');

        return new $class();
    }

    /**
     * 获取指定的一个数据库行记灵对象（单例）
     *
     * @param string $name 数据库行记灵对象名
     * @return \Be\System\Db\Tuple | mixed
     * @throws RuntimeException
     */
    public static function getTuple($name)
    {
        $key = 'Tuple:' . $name;
        if (isset(self::$cache[$key])) return self::$cache[$key];
        self::$cache[$key] = self::newTuple($name);
        return self::$cache[$key];
    }

    /**
     * 新创建一个数据库行记灵对象
     *
     * @param string $name 数据库行记灵对象名
     * @return \Be\System\Db\Tuple | mixed
     * @throws RuntimeException
     */
    public static function newTuple($name)
    {
        $class = 'Be\\Cache\\System\\Tuple\\' . $name;
        if (class_exists($class)) return (new $class());

        $service = self::getService('System.Db');
        $service->updateTuple($name);

        if (!class_exists($class)) {
            throw new RuntimeException('行记灵对象 ' . $name . ' 不存在！');
        }

        return (new $class());
    }

    /**
     * 获取指定的一个数据库表对象（单例）
     *
     * @param string $name 表名
     * @return \Be\System\Db\Table
     * @throws RuntimeException
     */
    public static function getTable($name)
    {
        $key = 'Table::' . $name;
        if (isset(self::$cache[$key])) return self::$cache[$key];
        self::$cache[$key] = self::newTable($name);
        return self::$cache[$key];
    }

    /**
     * 新创建一个数据库表对象
     *
     * @param string $name 表名
     * @return \Be\System\Db\Table
     * @throws RuntimeException
     */
    public static function newTable($name)
    {
        $class = 'Be\\Cache\\System\\Table\\' . $name;
        if (class_exists($class)) return (new $class());

        $service = self::getService('System.Db');
        $service->updateTable($name);

        if (!class_exists($class)) {
            throw new RuntimeException('表对象 ' . $name . ' 不存在！');
        }

        return (new $class());
    }

    /**
     * 获取指定的一个数据库表配置（单例）
     *
     * @param string $app 应用名
     * @param string $name 表名
     * @return \Be\System\Db\TableConfig
     * @throws RuntimeException
     */
    public static function getTableConfig($name)
    {
        $key = 'TableConfig:' . $name;
        if (isset(self::$cache[$key])) return self::$cache[$key];
        self::$cache[$key] = self::newTableConfig($name);
        return self::$cache[$key];
    }

    /**
     * 新创建一个数据库表配置
     *
     * @param string $app 应用名
     * @param string $name 表名
     * @return \Be\System\Db\TableConfig
     * @throws RuntimeException
     */
    public static function newTableConfig($name)
    {
        $key = 'TableConfig:' . $name;
        if (isset(self::$cache[$key])) return self::$cache[$key];

        $class = 'Be\\Data\\System\\TableConfig\\' . $name;
        if (class_exists($class)) {
            self::$cache[$key] = new $class();;
            return self::$cache[$key];
        }

        return new \Be\System\Db\TableConfig();
    }

    /**
     * 获取指定的一个自定义内容（单例）
     *
     * @param string $class 类名
     * @return string
     */
    public static function getHtml($class)
    {
        $key = 'Html:' . $class;
        if (isset(self::$cache[$key])) return self::$cache[$key];

        $path = self::$runtime->getCachePath() . '/System/Html/' . $class . '.html';
        if (!file_exists($path)) {
            $service = self::getService('System.Html');
            $service->update($class);
        }

        $html = '';
        if (file_exists($path)) {
            $html = file_get_contents($path);
        }

        self::$cache[$key] = $html;
        return self::$cache[$key];
    }

    /**
     * 获取指定的一个菜单（单例）
     *
     * @param string $menu 菜单名
     * @return Menu
     * @throws RuntimeException
     */
    public static function getMenu($menu)
    {
        $key = 'Menu:' . $menu;
        if (isset(self::$cache[$key])) return self::$cache[$key];

        $class = 'Be\\Cache\\System\\Menu\\' . $menu;
        if (class_exists($class)) {
            self::$cache[$key] = new $class();
            return self::$cache[$key];
        }

        $path = self::$runtime->getCachePath() . '/System/Menu/' . $menu . '.php';
        $service = self::getService('System.Menu');
        $service->update($menu);
        include_once $path;

        if (!class_exists($class)) {
            throw new RuntimeException('菜单 ' . $menu . ' 不存在！');
        }

        self::$cache[$key] = new $class();
        return self::$cache[$key];
    }

    /**
     * 获取指定的一个角色信息（单例）
     *
     * @param int $roleId 角色ID
     * @return Role
     * @throws RuntimeException
     */
    public static function getRole($roleId)
    {
        $key = 'Role:' . $roleId;
        if (isset(self::$cache[$key])) return self::$cache[$key];

        $class = 'Be\\Cache\\System\\Role\\Role' . $roleId;
        if (class_exists($class)) {
            self::$cache[$key] = new $class();
            return self::$cache[$key];
        }

        $path = self::$runtime->getCachePath() . '/System/Role/Role' . $roleId . '.php';
        $service = self::getService('System.Role');
        $service->updateRole($roleId);
        include_once $path;

        if (!class_exists($class)) {
            throw new RuntimeException('前台角色 #' . $roleId . ' 不存在！');
        }

        self::$cache[$key] = new $class();
        return self::$cache[$key];
    }

    /**
     * 获取指定的一个角色信息（单例）
     *
     * @param int $roleId 角色ID
     * @return Role
     * @throws RuntimeException
     */
    public static function getAdminRole($roleId)
    {
        $key = 'AdminRole:' . $roleId;
        if (isset(self::$cache[$key])) return self::$cache[$key];

        $class = 'Be\\Cache\\System\\AdminRole\\AdminRole' . $roleId;
        if (class_exists($class)) {
            self::$cache[$key] = new $class();
            return self::$cache[$key];
        }

        $path = self::$runtime->getCachePath() . '/System/AdminRole/AdminRole' . $roleId . '.php';
        $service = self::getService('System.AdminRole');
        $service->updateAdminRole($roleId);
        include_once $path;

        if (!class_exists($class)) {
            throw new RuntimeException('后台角色 #' . $roleId . ' 不存在！');
        }

        self::$cache[$key] = new $class();
        return self::$cache[$key];
    }

    /**
     * 获取一个属性（单例）
     *
     * @param string $name 名称
     * @return mixed
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
        $app = array_shift($parts);

        if ($theme === null) {
            $appProperty = Be::getProperty('App.' . $app);
            if (isset($appProperty->theme)) {
                $theme = $appProperty->theme;
            } else {
                $config = Be::getConfig('System.System');
                $theme = $config->theme;
            }
        }

        $class = 'Be\\Cache\\System\\Template\\' . $theme . '\\' . $app . '\\' . implode('\\', $parts);
        if (isset(self::$cache[$class])) return self::$cache[$class];

        $path = self::$runtime->getCachePath() . '/System/Template/' . $theme . '/' . $app . '/' . implode('/', $parts) . '.php';
        if (!file_exists($path)) {
            $service = self::getService('System.Template');
            $service->update($app, $template, $theme);
        }

        if (!class_exists($class)) throw new RuntimeException('模板（' . $app . '/' . $template . '）不存在！');

        self::$cache[$class] = new $class();
        return self::$cache[$class];
    }

    /**
     * 获取指定的一个模板（单例）
     *
     * @param string $template 模板名
     * @param string $theme 主题名
     * @return Template
     * @throws RuntimeException
     */
    public static function getAdminTemplate($template, $theme = null)
    {
        $parts = explode('.', $template);
        $app = array_shift($parts);

        if ($theme === null) {
            $appProperty = Be::getProperty('App.' . $app);
            if (isset($appProperty->theme)) {
                $theme = $appProperty->theme;
            } else {
                $config = Be::getConfig('System.Admin');
                $theme = $config->theme;
            }
        }

        $class = 'Be\\Cache\\System\\AdminTemplate\\' . $theme . '\\' . $app . '\\' . implode('\\', $parts);
        if (isset(self::$cache[$class])) return self::$cache[$class];

        $path = self::$runtime->getCachePath() . '/System/AdminTemplate/' . $theme . '/' . $app . '/' . implode('/', $parts) . '.php';
        if (!file_exists($path)) {
            $serviceSystem = self::getService('System.Template');
            $serviceSystem->update($app, $template, $theme, true);
        }

        if (!class_exists($class)) {
            throw new RuntimeException('后台模板（' . $app . '/' . $template . '）不存在！');
        }

        self::$cache[$class] = new $class();
        return self::$cache[$class];
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
                unset($user->password);
                unset($user->salt);
                unset($user->remember_me_token);
                unset($user->token);
            }
        }

        self::$cache[$key] = new User($user);
        return self::$cache[$key];
    }

    /**
     * 获取后台管理员用户 实例（单例）
     *
     * @param int $id 用户编号
     * @return AdminUser | mixed
     */
    public static function getAdminUser($id = 0)
    {
        $key = 'AdminUser:' . $id;
        if (isset(self::$cache[$key])) return self::$cache[$key];

        $user = null;
        if ($id == 0) {
            $user = Session::get('_adminUser');
        } else {
            $user = self::getTuple('system_admin_user')->load($id)->toObject();
            if ($user != null) {
                unset($user->password);
                unset($user->salt);
                unset($user->remember_me_token);
            }
        }

        self::$cache[$key] = new AdminUser($user);;
        return self::$cache[$key];
    }

    /**
     * 设置工厂缓存数据
     *
     * @param string $key 缓存key
     * @param mixed $value 缓存内容
     */
    public static function setCache($key, $value)
    {
        self::$cache[$key] = $value;
    }

    /**
     * 清除工厂缓存数据
     *
     * @param string $key 指定缓存key，未指定时清除所有缓存数据
     */
    public static function cleanCache($key = null)
    {
        if ($key === null) {
            self::$cache = [];
        } else {
            unset(self::$cache[$key]);
        }
    }

    public static function getRuntime()
    {
        if (self::$runtime == null) {
            self::$runtime = new Runtime();
        }
        return self::$runtime;
    }

}
