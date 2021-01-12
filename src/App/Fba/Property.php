<?php

namespace Be\Mf\App\Fba;


class Property extends \Be\System\App\Property
{

    public $label = 'Fba';
    public $icon = 'el-icon-fa fa-rocket';

    public function __construct() {
        parent::__construct(__FILE__);
    }

}

