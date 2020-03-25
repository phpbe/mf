<?php
namespace Be\System\Traits;

/**
 * 扩展基类
 */
Trait Event
{

    protected $events = [];

    /**
     * 监听事件
     * @param string $event 事件名
     * @param callable $callback 回调
     */
    public function on($event, $callback) {
        if (isset($this->events[$event])) {
            if (is_array($this->events[$event])) {
                $this->events[$event][] = $callback;
            } else {
                $this->events[$event] = [$this->events[$event], $callback];
            }
        } else {
            $this->events[$event] = $callback;
        }
    }

    /**
     * 触发事件
     * @param string $event 事件名
     * @param array ...$args 事件参数
     */
    public function trigger($event, ...$args) {
        if (isset($this->events[$event])) {
            if (is_array($this->events[$event])) {
                foreach ($this->events[$event] as $callback) {
                    if (is_callable($callback)) {
                        $callback(...$args);
                    }
                }
            } else {
                $callback = $this->events[$event];
                if (is_callable($callback)) {
                    $callback(...$args);
                }
            }
        }
    }

}
