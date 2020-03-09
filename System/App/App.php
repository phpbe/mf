<?php
namespace Be\System\App;

/**
 * 应用基类， 所有应用都从本类继承
 */
abstract class App
{
	protected $id = 0; // 应用在BE网站上的编号, 以便升级更新
    protected $name = null; // 应用名
    protected $label = ''; // 中文标识名， 如 '用户管理系统'
    protected $icon = ''; // 应用图标
    protected $version = '1.0'; // 当前版本号

    /**
     * 构造函数
     */
    public function __construct()
    {
        $class = get_called_class();
        $name = substr($class, 0, strrpos($class, '\\'));
        $name = substr($name, strrpos($name, '\\')+1);
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return null
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * 查看应用是否已安装
     *
     * @return bool
     */
    public function isInstalled()
    {
        return true;
    }

    public function __get($name) {
        if( isset( $this->$name ) ) {
            return $this->$name;
        } else {
            trigger_error( $name . ' 属性未定义',  E_USER_NOTICE );
        }
    }

}
