<?php

namespace Be\System;

/**
 * 扩展基类
 */
abstract class Plugin
{
    use \Be\System\Traits\Event;

    /**
     * 执行
     *
     * @param array $setting
     * @return mixed
     */
    abstract function execute($setting= []);
}
