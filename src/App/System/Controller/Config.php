<?php

namespace Be\App\System\Controller;

use Be\System\Be;

/**
 * @BeMenuGroup("系统配置", ordering="20")
 * @BePermissionGroup("系统配置", ordering="20")
 */
class Config extends \Be\System\Controller
{

    /**
     * @BeMenu("系统配置", icon="el-icon-setting", ordering="20")
     * @BePermission("系统配置", ordering="20")
     */
    public function dashboard()
    {
        Be::getPlugin('Config')->setting(['appName' => 'System'])->execute();
    }


}