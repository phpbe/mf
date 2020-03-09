<?php
namespace Be\App\System\Config;

/**
 * @be-config-label 已安装的资源
 */
class Installed
{

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemArrayString
     * @be-config-item-label 已安装的应用
     */
    public $apps = ['System'];

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemArrayString
     * @be-config-item-label 已安装的主题
     */
    public $themes = ['Default'];

}
