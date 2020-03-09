<?php
namespace App\System\Config;

/**
 * @be-config-label 日志
 */
class Log
{

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemString
     * @be-config-item-label 系统日志级别
     * @be-config-item-keyValues ["debug":"debug","info":"info","notice":"notice","warning":"warning","error":"error","critical":"critical","alert":"alert","emergency":"emergency"]
     */
    public $level = 'debug';

}
