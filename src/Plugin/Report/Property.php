<?php

namespace Be\Mf\Plugin\Report;


class Property extends \Be\F\Property\Driver
{

    public $label = '报表';

    public function __construct() {
        parent::__construct(__FILE__);
    }

}

