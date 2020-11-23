<?php

namespace Be\Theme\Installer;


class Property extends \Be\System\Theme\Property
{

    public $label = '安装器主题';

    public function __construct() {
        parent::__construct(__FILE__);
    }

}

