<?php

namespace Be\Mf\Plugin\Form;


class Property extends \Be\F\Property\Driver
{

    public $label = '表单';

    public function __construct() {
        parent::__construct(__FILE__);
    }

}

