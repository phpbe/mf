<?php
namespace Be\App\System\Config;

/**
 * @BeConfig("系统")
 */
class System
{
    /**
     * @BeConfigItem("是否开启伪静态", driver="\Be\\Plugin\Config\Item\ConfigItemBool")
     */
    public $urlRewrite = true;

    /**
     * @BeConfigItem("伪静态页后辍", driver="\Be\\Plugin\Config\Item\ConfigItemString")
     */
    public $urlSuffix = '.html';

    /**
     * @BeConfigItem("主题",
     *     driver="\Be\\Plugin\Config\Item\ConfigItemString",
     *     keyValueType = "code",
     *     keyValues = "\Be\System\Be::getService('System.Theme')->getThemeKeyValues()")
     */
    public $theme = 'Admin';

    /**
     * @BeConfigItem("允许上传的文件类型", driver="\Be\\Plugin\Config\Item\ConfigItemArrayString")
     */
    public $allowUploadFileTypes = ['jpg', 'jpeg', 'gif', 'png', 'txt', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'rar'];

    /**
     * @BeConfigItem("允许上传的图片类型", driver="\Be\\Plugin\Config\Item\ConfigItemArrayString")
     */
    public $allowUploadImageTypes = ['jpg', 'jpeg', 'gif', 'png'];

    /**
     * @BeConfigItem("时区", driver="\Be\\Plugin\Config\Item\ConfigItemString")
     */
    public $timezone = 'Asia/Shanghai';

    /**
     * @BeConfigItem("时区",
     *     driver="\Be\\Plugin\Config\Item\ConfigItemString",
     *     ui="[':min' => 1]")
     */
    public $pageSize = 12;

    /**
     * @BeConfigItem("是否开启开发者模式", driver="\Be\\Plugin\Config\Item\ConfigItemBool")
     */
    public $developer = true;

}
