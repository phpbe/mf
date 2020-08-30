<?php

namespace Be\System;

/**
 * 扩展基类
 */
abstract class Plugin
{
    use \Be\System\Traits\Event;

    protected $setting = null;

    /**
     * 配置项
     *
     * @param array $setting
     * @return Plugin
     */
    public function setting($setting = [])
    {
        $this->setting = $setting;
        return $this;
    }

    /**
     * 执行指定任务
     *
     * @param string $task
     */
    public function execute($task = null)
    {
        if ($task === null) {
            $task = Request::request('task', 'display');
        }

        if (method_exists($this, $task)) {
            $this->$task();
        }
    }

    /**
     * 默认畀出方法
     */
    public function display() {

    }

}
