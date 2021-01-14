<?php

namespace Be\Mf\Plugin\Operation;


class Property extends \Be\F\Property\Driver
{

    public $label = '操作';

    public function __construct() {
        parent::__construct(__FILE__);
    }

}

