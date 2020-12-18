<?php

namespace Be\App\System\Service;

use Be\System\Be;
use Be\System\Exception\ServiceException;

class App
{

    private $apps = null;

    /**
     * @return array|null
     */
    public function getApps()
    {
        if ($this->apps == null) {
            $this->apps = Be::getDb()->getKeyObjects('SELECT * FROM system_app', null, 'name');
        }

        return $this->apps;
    }

    /**
     * @return array
     */
    public function getAppNames()
    {
        return array_keys($this->getApps());
    }

    /**
     * @return int
     */
    public function getAppCount()
    {
        return count($this->getApps());
    }

    /**
     * @return array
     */
    public function getAppNameLabelKeyValues()
    {
        return array_column($this->getApps(), 'label', 'name');
    }

    /**
     * @param string $appName 应应用名
     * @return bool
     * @throws ServiceException
     */
    public function install($appName)
    {
        try {
            $exist = Be::getTuple('system_app')->loadBy('name', $appName);
            throw new ServiceException('应用已于' . $exist->install_time . '安装过！');
        } catch (\Throwable $t) {

        }

        $class = '\\Be\\App\\' . $appName . '\\Installer';
        if (class_exists($class)) {
            /**
             * @var \Be\System\App\Installer $installer
             */
            $installer = new $class();
            $installer->install();
        }

        $lastOrdering = Be::getDb()->getValue('SELECT ordering FROM system_app ORDER BY ordering DESC LIMIT 1');
        if (!$lastOrdering) {
            $lastOrdering = 0;
        }

        $property = Be::getProperty('App.' . $appName);
        Be::getDb()->insert('system_app', [
            'name' => $property->getName(),
            'label' => $property->getLabel(),
            'icon' => $property->getIcon(),
            'ordering' => $lastOrdering + 1,
            'install_time' => date('Y-m-d H:i:s')
        ]);

        return true;
    }

    /**
     * 卸载应用
     *
     * @param string $appName 应应用名
     * @return bool
     * @throws ServiceException
     */
    public function uninstall($appName)
    {
        $exist = null;
        try {
            $exist = Be::getTuple('system_app')->loadBy('name', $appName);
        } catch (\Throwable $t) {
            throw new ServiceException('该应用尚未安装！');
        }

        $class = '\\Be\\App\\' . $appName . '\\UnInstaller';
        if (class_exists($class)) {
            /**
             * @var \Be\System\App\UnInstaller $unInstaller
             */
            $unInstaller = new $class();
            $unInstaller->uninstall();
        }

        $exist->delete();
        return true;
    }


}
