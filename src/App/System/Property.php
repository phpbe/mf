<?php

namespace Be\Mf\App\System;


class Property extends \Be\F\App\Property
{

    protected $label = '系统';
    protected $icon = 'el-icon-s-tools';
    protected $description = '系统基本管理功能';

    public function __construct() {
        parent::__construct(__FILE__);
    }

}
