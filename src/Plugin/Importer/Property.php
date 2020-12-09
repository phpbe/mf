<?php

namespace Be\Plugin\Importer;


class Property extends \Be\System\Plugin\Property
{

    public $label = '导入器';

    public function __construct() {
        parent::__construct(__FILE__);
    }

}

