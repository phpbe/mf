<?php

namespace Be\Mf\App\System\Controller;

use Be\Mf\Be;

/**
 * @BeMenuGroup("管理")
 * @BePermissionGroup("管理")
 */
class Task
{
    /**
     * @BeMenu("计划任务", icon="el-icon-timer", ordering="2.3")
     * @BePermission("计划任务", ordering="2.3")
     */
    public function dashboard()
    {
        Be::getPlugin('Task')->setting(['appName' => 'System'])->execute();
    }
}
