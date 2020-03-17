<?php
namespace Be\System;

/**
 * 后台控制器基类
 */
abstract class Event
{

    private static $events = [];

    /**
     * 监听事件
     * @param string $event 事件名
     * @param callable $callback 回调
     */
    public static function on($event, $callback) {
        if (isset(self::$events[$event])) {
            if (is_array(self::$events[$event])) {
                self::$events[$event][] = $callback;
            } else {
                self::$events[$event] = [self::$events[$event], $callback];
            }
        } else {
            self::$events[$event] = $callback;
        }
    }

    /**
     * 触发事件
     * @param string $event 事件名
     * @param array ...$args 事件参数
     */
    public static function trigger($event, ...$args) {
        if (isset(self::$events[$event])) {
            if (is_array(self::$events[$event])) {
                foreach (self::$events[$event] as $callback) {
                    if (is_callable($callback)) {
                        $callback(...$args);
                    }
                }
            } else {
                $callback = self::$events[$event];
                if (is_callable($callback)) {
                    $callback(...$args);
                }
            }
        }
    }

}
