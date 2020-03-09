<?php
namespace Be\Lib;

/**
 * 工厂方法
 *
 * @package Be\Lib\Image
 * @author liu12 <i@liu12.com>
 */
class Factory
{
    private static $cache = [];
    public static function singleton($lib) {
        if (!isset(self::$cache[$lib])) {
        	$class = $lib . '\\' . $lib;
            self::$cache[$lib] = new $class();
        }
        return self::$cache[$lib];
    }
}
