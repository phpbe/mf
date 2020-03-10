<?php
namespace Be\System;

/**
 * 应用基类， 所有应用都从本类继承
 */
abstract class Property
{
    protected $id = 0; // 应用在BE网站上的编号, 以便升级更新
    protected $name = null; // 应用名
    protected $label = ''; // 中文标识名， 如 '用户管理系统'
    protected $icon = ''; // 应用图标
    protected $version = '1.0'; // 当前版本号

    public $path = null; // 路径，相对于根路径

    /**
     * 构造函数
     */
    public function __construct()
    {
        $class = get_called_class();
        $name = substr($class, 0, strrpos($class, '\\'));
        $name = substr($name, strrpos($name, '\\')+1);
        $this->name = $name;

        $this->path = str_replace(Be::getRuntime()->getRootPath(), '', getcwd());
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

    public function __get($name) {
        if( isset( $this->$name ) ) {
            return $this->$name;
        } else {
            trigger_error( $name . ' 属性未定义',  E_USER_NOTICE );
        }
    }

}
