<?php

namespace Be\System;

/**
 * 扩展基类
 */
abstract class Plugin
{
    use \Be\System\Traits\Event;

    protected $setting = null;

    public function execute($setting = [])
    {
        $this->setting = $setting;
    }

}
