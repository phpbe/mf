<?php
namespace Be\System;

/**
 * 属性基类
 */
abstract class Property
{
    protected $name = null; // 名称
    protected $label = ''; // 中文名
    protected $icon = ''; // 图标

    public $path = null; // 路径，相对于根路径

    /**
     * 构造函数
     * @param string $path 文件咱径
     */
    public function __construct($path = '')
    {
        $class = get_called_class();
        $name = substr($class, 0, strrpos($class, '\\'));
        $name = substr($name, strrpos($name, '\\')+1);
        $this->name = $name;

        $this->path = str_replace(Be::getRuntime()->getRootPath(), '', substr($path, 0, strrpos($path, DIRECTORY_SEPARATOR)));
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
     * @return null | string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * 获取网址
     *
     * @return string
     */
    public function getUrl() {
        return Be::getRuntime()->getRootUrl() . $this->path;
    }

    public function __get($name) {
        if( isset( $this->$name ) ) {
            return $this->$name;
        } else {
            trigger_error( $name . ' 属性未定义',  E_USER_NOTICE );
        }
    }

}
