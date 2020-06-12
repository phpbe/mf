<?php

namespace Be\Plugin\FileManager;


class Property extends \Be\System\Plugin\Property
{

    public $label = '文件管理器';

    public function __construct() {
        parent::__construct(__FILE__);
    }

}

