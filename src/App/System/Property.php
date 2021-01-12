<?php

namespace Be\Mf\App\System;


class Property extends \Be\System\App\Property
{

    public $label = '系统';
    public $icon = 'el-icon-s-tools';

    public function __construct() {
        parent::__construct(__FILE__);
    }

}
