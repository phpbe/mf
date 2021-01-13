<?php

namespace Be\Mf;

use Be\F\Cache\CacheFactory;
use Be\F\Config\ConfigFactory;
use Be\F\Db\DbFactory;
use Be\F\Db\TableFactory;
use Be\F\Db\TablePropertyFactory;
use Be\F\Db\TupleFactory;
use Be\F\Lib\LibFactory;
use Be\F\Logger\LoggerFactory;
use Be\F\Property\PropertyFactory;
use Be\F\Request\RequestFactory;
use Be\F\Response\ResponseFactory;
use Be\F\Runtime\RuntimeFactory;
use Be\F\Runtime\RuntimeException;
use Be\F\Session\SessionFactory;
use Be\F\Template\TemplateFactory;
use Be\Mf\App\ServiceFactory;
use Be\MF\Plugin\PluginFactory;

/**
 *  BE系统资源工厂
 * @package System
 *
 */
abstract class Be extends \Be\F\Be
{

    /**
     * 获取运行时对象
     *
     * @return \Be\F\Runtime\Driver
     */
    public static function getRuntime()
    {
        return RuntimeFactory::getInstance();
    }

    /**
     * 获取请求对象
     *
     * @return \Be\F\Request\Driver
     */
    public static function getRequest()
    {
        return RequestFactory::getInstance();
    }

    /**
     * 获取输出对象
     *
     * @return \Be\F\Response\Driver
     */
    public static function getResponse()
    {
        return ResponseFactory::getInstance();
    }

    /**
     * 获取指定的配置文件（单例）
     *
     * @param string $name 配置文件名
     * @return mixed
     */
    public static function getConfig($name)
    {
        return ConfigFactory::getInstance($name);
    }

    /**
     * 新创建一个指定的配置文件
     *
     * @param string $name 配置文件名
     * @return mixed
     */
    public static function newConfig($name)
    {
        return ConfigFactory::newInstance($name);
    }

    /**
     * 获取日志记录器
     *
     * @return \Be\F\Logger\Driver
     */
    public static function getLogger()
    {
        return LoggerFactory::getInstance();
    }

    /**
     * 获取一个属性（单例）
     *
     * @param string $name 名称
     * @return \Be\F\Property\Driver
     * @throws RuntimeException
     */
    public static function getProperty($name)
    {
        return PropertyFactory::getInstance($name);
    }


    /**
     * 获取SESSION
     *
     * @return \Be\F\Session\Driver
     */
    public static function getSession()
    {
        return SessionFactory::getInstance();
    }

    /**
     * 获取Cache
     *
     * @return \Be\F\Cache\Driver
     */
    public static function getCache()
    {
        return CacheFactory::getInstance();
    }

    /**
     * 获取数据库对象（单例）
     *
     * @param string $name 数据库名
     * @return \Be\F\Db\Driver
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
     * @return \Be\F\Db\Driver
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
     * @return \Be\F\Db\Driver
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
     * @return \Be\F\Db\Tuple | mixed
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
     * @return \Be\F\Db\Tuple | mixed
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
     * @return \Be\F\Db\Table
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
     * @return \Be\F\Db\Table
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
     * @return \Be\F\Db\TableProperty
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
     * 获取指定的一个服务（单例）
     *
     * @param string $name 服务名
     * @return mixed
     */
    public static function getService($name)
    {
        return ServiceFactory::getInstance($name);
    }

    /**
     * 新创建一个服务
     *
     * @param string $name 服务名
     * @return mixed
     */
    public static function newService($name)
    {
        return ServiceFactory::newInstance($name);
    }

    /**
     * 获取指定的一个模板（单例）
     *
     * @param string $template 模板名
     * @param string $theme 主题名
     * @return \Be\F\Template\Driver
     * @throws RuntimeException
     */
    public static function getTemplate($template, $theme = null)
    {
        return TemplateFactory::getInstance($template, $theme);
    }

    /**
     * 获取指定的一个菜单（单例）
     *
     * @return \Be\F\Menu\Driver
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

    /**
     * 调用未声明的方法
     *
     * @param $name
     * @param $arguments
     * @return null
     */
    public static function __callStatic($name, $arguments)
    {
        $prefix = substr($name, 0, 3);
        $module = substr($name, 3);
        $factory = '\\Be\\Framework\\' . $module . '\\' . $module . 'Factory';
        if ($prefix == 'get') {
            if (is_callable([$factory, 'getInstance'])) {
                return $factory::getInstance(...$arguments);
            }
        } elseif ($prefix == 'new') {
            if (is_callable([$factory, 'newInstance'])) {
                return $factory::newInstance(...$arguments);
            }
        }

        return null;
    }
}
