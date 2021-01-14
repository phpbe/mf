<?php

namespace Be\Mf\Plugin\Config;


class Property extends \Be\F\Property\Driver
{

    public $label = '配置';

    public function __construct() {
        parent::__construct(__FILE__);
    }

}

