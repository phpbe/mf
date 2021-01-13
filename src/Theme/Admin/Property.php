<?php

namespace Be\Mf\Theme\Admin;


class Property extends \Be\F\Property\Driver
{

    public $label = '默认主题';

    public function __construct() {
        parent::__construct(__FILE__);
    }

}

