<?php

namespace Be\Mf\Menu;

use Be\Mf\Be;


/**
 * Menu 工厂
 */
abstract class MenuFactory
{

    private static $cache = [];

    /**
     * 获取Runtime实例
     *
     * @return Driver
     */
    public static function getInstance()
    {
        if (isset(self::$cache['Menu'])) return self::$cache['Menu'];

        $path = Be::getRuntime()->getCachePath() . '/Menu.php';
        if (!file_exists($path)) {
            $service = Be::getService('System.Menu');
            $service->update();
            include_once $path;
        }

        $class = 'Be\\Mf\\Cache\\Menu';
        self::$cache['Menu'] = new $class();
        return self::$cache['Menu'];
    }


}
