<?php

namespace Be\App\System;


class Property extends \Be\System\App\Property
{

    public $label = '系统';
    public $icon = 'appstore';

    public function __construct() {
        parent::__construct(__FILE__);
    }

}

