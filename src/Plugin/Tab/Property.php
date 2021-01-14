<?php

namespace Be\Mf\Plugin\Tab;


class Property extends \Be\F\Property\Driver
{

    public $label = '选项卡';

    public function __construct() {
        parent::__construct(__FILE__);
    }

}

