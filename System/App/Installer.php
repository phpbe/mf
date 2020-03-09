<?php
namespace Be\System\App;

/**
 * 应用安装器
 */
abstract class Installer
{

    protected $app = null; // 应用名

    /**
     * 构造函数
     */
    public function __construct()
    {
        $class = get_called_class();
        $app = substr($class, 0, strrpos($class, '\\'));
        $app = substr($app, strrpos($app, '\\')+1);
        $this->app = $app;
    }

    /**
     * 安装时需要执行的操作，如创建数据库表
     */
	public function install()
	{

	}

}
