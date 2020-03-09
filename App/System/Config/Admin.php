<?php
namespace Be\App\System\Config;

/**
 * @be-config-label 后台参数
 */
class Admin
{

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemInt
     * @be-config-item-label 默认分页显示条数
     * @be-config-item-ui {":min":1}
     */
    public $pageSize = 12;


    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemString
     * @be-config-item-label 默认主题
     */
    public $theme = 'admin';

}
