<?php
namespace Be\App\System\Config;

/**
 * @BeConfig("系统")
 */
class System
{
    /**
     * @BeConfigItem("是否开启伪静态", driver="ConfigItemSwitch")
     */
    public $urlRewrite = true;

    /**
     * @BeConfigItem("伪静态页后辍", driver="ConfigItemInput")
     */
    public $urlSuffix = '.html';

    /**
     * @BeConfigItem("主题",
     *     driver="ConfigItemSelect",
     *     keyValues = "return \Be\System\Be::getService('System.Theme')->getThemeKeyValues();")
     */
    public $theme = 'Admin';

    /**
     * @BeConfigItem("允许上传的文件类型", driver="ConfigItemInputTextArea", valueType = "array(string)")
     */
    public $allowUploadFileTypes = ['jpg', 'jpeg', 'gif', 'png', 'txt', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'rar'];

    /**
     * @BeConfigItem("允许上传的图片类型", driver="ConfigItemInputTextArea")
     */
    public $allowUploadImageTypes = ['jpg', 'jpeg', 'gif', 'png'];

    /**
     * @BeConfigItem("时区", driver="ConfigItemInput")
     */
    public $timezone = 'Asia/Shanghai';

    /**
     * @BeConfigItem("默认分页",
     *     driver="ConfigItemInputNumberInt",
     *     ui="return ['input-number' => [':min' => 1]];")
     */
    public $pageSize = 12;

    /**
     * @BeConfigItem("是否开启开发者模式", driver="ConfigItemSwitch")
     */
    public $developer = true;

}
