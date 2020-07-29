<?php
namespace Be\App\System\Config;

/**
 * @BeConfig("系统运行日志")
 */
class RuntimeLog
{

    /**
     * @BeConfigItem("日志级别",
     *     driver="\Be\\Plugin\Config\Item\ConfigItemMixed",
     *     values = "['debug','info','notice','warning','error','critical','alert','emergency']")
     */
    public $level = 'debug';

}
