<?php

namespace Be\Mf\Plugin\Importer;


class Property extends \Be\F\Property\Driver
{

    public $label = '导入器';

    public function __construct() {
        parent::__construct(__FILE__);
    }

}

