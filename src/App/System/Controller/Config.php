<?php

namespace Be\App\System\Controller;

use Be\System\Be;
use Be\System\Request;
use Be\System\Response;
use Be\Util\Net\FileUpload;

/**
 * @BeMenuGroup("系统配置")
 * @BePermissionGroup("系统配置")
 */
class Config extends \Be\System\Controller
{

    /**
     * @BeMenu("系统配置")
     * @BePermission("系统配置")
     */
    public function dashboard()
    {
        Be::getPlugin('Config')->setting(['appName' => 'System'])->execute();
    }


}