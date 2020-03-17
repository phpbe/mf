<?php
namespace Be\App\System\Service;

use Be\System\Be;
use Be\System\Exception\ServiceException;

class App extends \Be\System\Service
{

	private $apps = null;

    public function getApps()
    {
		if ($this->apps == null) {
			$this->apps = Be::getDb()->withCache(600)->getKeyObjects('SELECT * FROM system_app', null, 'name');
		}

        return $this->apps;
    }

    public function getAppNames()
    {
        return array_keys($this->getApps());
    }

	public function getAppCount()
    {
		return count($this->getApps());
    }

    public function getAppNameLabelKeyValues()
    {
        return array_column($this->getApps(), 'label', 'name');
    }
    
    // 安装应用文件
    public function install($app)
    {

        $db = Be::getDb();
        $exist = $db->getObject('SELECT * FROM system_app WHERE `name`=\''.$app.'\'');
        if ($exist) {
            throw new ServiceException('应用已于'.$exist->install_time.'安装过！');
        }


        $class = '\\Be\\App\\'.$app.'\\Installer';
        if (class_exists($class)) {
            $installer = new $class();
            $installer->install();
        }

        $property = Be::getProperty('App.'.$app);
        $db->insert('system_installed_app', [
            'name' => $property->name,
            'label' => $property->label,
            'icon' => $property->icon,
            'install_time' => date('Y-m-d H:i:s')
        ]);

		return true;
    }
    

    // 删除应用
    public function uninstall($app)
    {
        $db = Be::getDb();
        $exist = $db->getObject('SELECT * FROM system_app WHERE `name`=\''.$app.'\'');
        if (!$exist) {
            throw new ServiceException('该应用尚未安装！');
        }

        $class = '\\Be\\App\\'.$app.'\\UnInstaller';
        if (class_exists($class)) {
            $installer = new $class();
            $installer->uninstall();
        }

        $db->query('DELETE FROM system_app WHERE `name`=\''.$app.'\'');
        return true;
    }



}
