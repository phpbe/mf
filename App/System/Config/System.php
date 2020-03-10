<?php
namespace Be\App\System\Config;

/**
 * @be-config-label 系统
 */
class System
{

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemBool
     * @be-config-item-label 是否暂停网站
     */
    public $offline = false;

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemText
     * @be-config-item-label 暂停网站时显示的信息
     */
    public $offlineMessage = '<p>系统升级，请稍候访问。</p>';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemString
     * @be-config-item-label 网站名称
     */
    public $siteName = 'BE';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemBool
     * @be-config-item-label 是否开启伪静态
     */
    public $sef = true;

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemString
     * @be-config-item-label 伪静态页后辍
     */
    public $sefSuffix = '.html';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemString
     * @be-config-item-label 主题
     * @be-config-item-keyValueType code
     * @be-config-item-keyValues \Be\System\Be::getService('System.Theme')->getThemeKeyValues()
     */
    public $theme = 'huxiu';

    /**
     * 默认首页
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemMixed
     * @be-config-item-label 默认首页参数
     */
    public $homeParams = ['app'=>'Cms', 'controller'=>'Article', 'action'=>'home'];

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemString
     * @be-config-item-label 首页的标题
     */
    public $homeTitle = '首页';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemString
     * @be-config-item-label 首页的 meta keywords
     */
    public $homeMetaKeywords = 'Be easy';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemString
     * @be-config-item-label 首页的 meta description
     */
    public $homeMetaDescription = 'Be easy';

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
}
