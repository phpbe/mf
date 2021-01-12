<?php

namespace Be\Mf\App\System\Controller;

use Be\Mf\Be;

/**
 * @BeMenuGroup("系统配置", ordering="20")
 * @BePermissionGroup("系统配置", ordering="20")
 */
class Config
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