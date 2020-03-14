<?php
namespace Be\App\System\Config;

/**
 * @be-config-label 系统运行日志
 */
class RuntimeLog
{

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemString
     * @be-config-item-label 日志级别
     * @be-config-item-keyValues ["debug":"debug","info":"info","notice":"notice","warning":"warning","error":"error","critical":"critical","alert":"alert","emergency":"emergency"]
     */
    public $level = 'debug';

}
