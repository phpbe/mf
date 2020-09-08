<?php

namespace Be\Plugin\Config;


class Property extends \Be\System\Plugin\Property
{

    public $label = '配置';

    public function __construct() {
        parent::__construct(__FILE__);
    }

}

