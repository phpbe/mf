<?php

namespace Be\Theme\Admin;


class Property extends \Be\System\Theme\Property
{

    public $label = '默认主题';

    public function __construct() {
        parent::__construct(__FILE__);
    }

}

