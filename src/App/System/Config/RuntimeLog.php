<?php
namespace Be\App\System\Config;

/**
 * @BeConfig("系统运行日志")
 */
class RuntimeLog
{

    /**
     * @BeConfigItem("日志级别",
     *     driver="FormItemSelect",
     *     values = "return ['debug','info','notice','warning','error','critical','alert','emergency'];")
     */
    public $level = 'debug';

}
