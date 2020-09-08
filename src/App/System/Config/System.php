<?php
namespace Be\App\System\Config;

/**
 * @BeConfig("系统")
 */
class System
{
    /**
     * @BeConfigItem("是否开启伪静态", driver="FormItemSwitch")
     */
    public $urlRewrite = 1;

    /**
     * @BeConfigItem("伪静态页后辍", driver="FormItemInput")
     */
    public $urlSuffix = '.html';

    /**
     * @BeConfigItem("主题",
     *     driver="FormItemSelect",
     *     keyValues = "return \Be\System\Be::getService('System.Theme')->getThemeKeyValues();")
     */
    public $theme = 'Admin';

    /**
     * @BeConfigItem("允许上传的文件大小", driver="FormItemInput")
     */
    public $uploadMaxSize = '1M';

    /**
     * @BeConfigItem("允许上传的文件类型", driver="FormItemCode", language="json", valueType = "array(string)")
     */
    public $allowUploadFileTypes = ['jpg', 'jpeg', 'gif', 'png', 'txt', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'rar'];

    /**
     * @BeConfigItem("允许上传的图片类型", driver="FormItemCode", language="json", valueType = "array(string)")
     */
    public $allowUploadImageTypes = ['jpg', 'jpeg', 'gif', 'png'];

    /**
     * @BeConfigItem("时区", driver="FormItemInput")
     */
    public $timezone = 'Asia/Shanghai';

    /**
     * @BeConfigItem("默认分页",
     *     driver="FormItemInputNumberInt",
     *     ui="return ['input-number' => [':min' => 1]];")
     */
    public $pageSize = 10;

    /**
     * @BeConfigItem("是否开启开发者模式", driver="FormItemSwitch")
     */
    public $developer = true;

}
