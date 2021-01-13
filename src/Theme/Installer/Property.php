<?php

namespace Be\Mf\Theme\Installer;


class Property extends \Be\F\Property\Driver
{

    public $label = '安装器主题';

    public function __construct() {
        parent::__construct(__FILE__);
    }

}

