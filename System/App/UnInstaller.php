<?php
namespace Be\System\App;

use Be\System\Be;

/**
 * 应用卸载器
 */
abstract class UnInstaller
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
     * 删除时需要执行的操作，如删除数据库表
     */
	public function uninstall()
	{
        $appPath = Be::getRuntime()->getRootPath().'/app/'.$this->app;
        rmdir($appPath);
	}


}
