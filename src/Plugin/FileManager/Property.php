<?php

namespace Be\Mf\Plugin\FileManager;


class Property extends \Be\F\Property\Driver
{

    public $label = '文件管理器';

    public function __construct() {
        parent::__construct(__FILE__);
    }

}

