<?php
namespace Be\App\System\Service;

use Be\System\Be;
use Be\System\Exception\ServiceException;

class App extends \Be\System\Service
{

	private $apps = null;

    /**
     * @return array|null
     * @throws \Be\System\Exception\RuntimeException
     */
    public function getApps()
    {
		if ($this->apps == null) {
			$this->apps = Be::getDb()->withCache(600)->getKeyObjects('SELECT * FROM system_app', null, 'name');
		}

        return $this->apps;
    }

    /**
     * @return array
     * @throws \Be\System\Exception\RuntimeException
     */
    public function getAppNames()
    {
        return array_keys($this->getApps());
    }

    /**
     * @return int
     * @throws \Be\System\Exception\RuntimeException
     */
    public function getAppCount()
    {
		return count($this->getApps());
    }

    /**
     * @return array
     * @throws \Be\System\Exception\RuntimeException
     */
    public function getAppNameLabelKeyValues()
    {
        return array_column($this->getApps(), 'label', 'name');
    }
    
    // 安装应用文件

    /**
     * @param $app
     * @return bool
     * @throws ServiceException
     * @throws \Be\System\Exception\DbException
     * @throws \Be\System\Exception\RuntimeException
     */
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
        $db->insert('system_app', [
            'name' => $property->name,
            'label' => $property->label,
            'icon' => $property->icon,
            'install_time' => date('Y-m-d H:i:s')
        ]);

		return true;
    }
    
    /**
     * 删除应用
     *
     * @param $app
     * @return bool
     * @throws ServiceException
     * @throws \Be\System\Exception\DbException
     * @throws \Be\System\Exception\RuntimeException
     */
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
