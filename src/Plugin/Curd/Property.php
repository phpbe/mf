<?php

namespace Be\Mf\Plugin\Curd;


class Property extends \Be\F\Property\Driver
{

    public $label = '增删改查';

    public function __construct() {
        parent::__construct(__FILE__);
    }

}

