<?php
namespace Be\App\System\Config;

/**
 * @be-config-label 系统
 */
class System
{

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemBool
     * @be-config-item-label 是否开启伪静态
     */
    public $urlRewrite = true;

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemString
     * @be-config-item-label 伪静态页后辍
     */
    public $urlSuffix = '.html';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemString
     * @be-config-item-label 主题
     * @be-config-item-keyValueType code
     * @be-config-item-keyValues \Be\System\Be::getService('System.Theme')->getThemeKeyValues()
     */
    public $theme = 'Admin';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemArrayString
     * @be-config-item-label 允许上传的文件类型
     */
    public $allowUploadFileTypes = ['jpg', 'jpeg', 'gif', 'png', 'txt', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'rar'];

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemArrayString
     * @be-config-item-label 允许上传的图片类型
     */
    public $allowUploadImageTypes = ['jpg', 'jpeg', 'gif', 'png'];

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemString
     * @be-config-item-label 时区
     */
    public $timezone = 'Asia/Shanghai';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemInt
     * @be-config-item-label 默认分页显示条数
     * @be-config-item-ui {":min":1}
     */
    public $pageSize = 12;

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemBool
     * @be-config-item-label 是否开启开发者模式
     */
    public $developer = true;

}
