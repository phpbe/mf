<?php

namespace Be\Plugin\FileManager;

use Be\Plugin\Lists\Field\FieldItemText;
use Be\System\Be;
use Be\System\Db\Tuple;
use Be\System\Plugin;
use Be\System\Request;
use Be\System\Response;
use Be\System\Cookie;

/**
 * 文件管理器
 *
 * Class FileManager
 * @package Be\Plugin\FileManager
 */
class FileManager extends Plugin
{


    protected $setting = null;


    /**
     * @param array $setting
     */
    public function execute($setting = [])
    {
        $this->setting = $setting;
    }





    /**
     * 删除
     *
     * @param array $setting 配置项
     */
    public function browser($setting = [])
    {

        //$rootPath


    }



}

