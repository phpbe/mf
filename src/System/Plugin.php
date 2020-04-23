<?php

namespace Be\System;

/**
 * 扩展基类
 */
abstract class Plugin
{
    use \Be\System\Traits\Event;

    public function execute($setting = [])
    {
    }

}
