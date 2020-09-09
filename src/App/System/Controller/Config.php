<?php

namespace Be\App\System\Controller;

use Be\System\Be;

/**
 * @BeMenuGroup("系统配置")
 * @BePermissionGroup("系统配置")
 */
class Config extends \Be\System\Controller
{

    /**
     * @BeMenu("配置中心", icon="el-icon-setting")
     * @BePermission("配置中心")
     */
    public function dashboard()
    {
        Be::getPlugin('Config')->setting(['appName' => 'System'])->execute();
    }


}