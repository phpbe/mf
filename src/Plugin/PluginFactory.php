<?php

namespace Be\Mf\Plugin;

/**
 * Plugin 工厂
 */
abstract class PluginFactory
{

    private static $cache = [];

    /**
     * 获取指定的一个扩展
     *
     * @param string $name 扩展名
     * @return mixed
     */
    public static function getInstance($name)
    {
        $cid = \Swoole\Coroutine::getuid();
        if (isset(self::$cache[$cid][$name])) return self::$cache[$cid][$name];
        self::$cache[$cid][$name] = self::newInstance($name);
        return self::$cache[$cid][$name];
    }

    /**
     * 新创建一个指定的扩展
     *
     * @param string $name 扩展名
     * @return mixed
     * @throws PluginException
     */
    public static function newInstance($name)
    {
        $class = 'Be\\Mf\\Plugin\\' . $name . '\\' . $name;
        if (!class_exists($class)) {
            throw new PluginException('扩展 ' . $name . ' 不存在！');
        }

        return new $class();
    }

    /**
     * 回收资源
     */
    public static function release()
    {
        $cid = \Swoole\Coroutine::getuid();
        unset(self::$cache[$cid]);
    }

}
