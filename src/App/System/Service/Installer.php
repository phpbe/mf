<?php

namespace Be\Mf\App\System\Service;

use Be\Mf\Be;
use Be\F\App\ServiceException;

class Installer
{

    /**
     * 测试数据库连接
     *
     * @param $data
     * @return array|null 返回库名列表
     * @throws ServiceException
     */
    public function testDb($data)
    {
        $host = $data['host'];
        $port = $data['port'];
        $username = $data['username'];
        $password = $data['password'];

        $options = array(
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
        );

        $connection = null;
        try {
            $dsn = 'mysql:host=' . $host . ';port=' . $port;
            $connection = new \PDO($dsn, $username, $password, $options);
        } catch (\Throwable $t) {
            throw new ServiceException('连接数据库失败：' . $t->getMessage());
        }

        $values = null;
        try {
            $sql = 'SELECT `SCHEMA_NAME` FROM information_schema.SCHEMATA WHERE `SCHEMA_NAME`!=\'information_schema\'';
            $statement = $connection->query($sql);
            $values = $statement->fetchAll(\PDO::FETCH_COLUMN);
            $statement->closeCursor();
        } catch (\Throwable $t) {
            throw new ServiceException('连接数据库成功，但获取库名列表失败：' . $t->getMessage());
        }

        return $values;
    }

    public function getAppNames()
    {
        $apps = [];
        $vendorPath = Be::getRuntime()->getRootPath() . '/vendor';
        $dirs = scandir($vendorPath);
        foreach ($dirs as $dir) {
            if ($dir != '..' && $dir != '.') {
                $subVendorPath = $vendorPath . '/' . $dir;
                if (is_dir($subVendorPath)) {
                    $subDirs = scandir($subVendorPath);
                    foreach ($subDirs as $subDir) {
                        if ($subDir != '..' && $subDir != '.') {
                            $propertyPath = $subVendorPath . '/' . $subDir . '/src/Property.php';
                            if (!file_exists($propertyPath)) {
                                $propertyPath = $subVendorPath . '/' . $subDir . '/Property.php';
                            }

                            if (file_exists($propertyPath)) {
                                $content = file_get_contents($propertyPath);
                                preg_match('/namespace\s+Be\\\\Mf\\\\App\\\\(\w+)/i', $content, $matches);
                                if (isset($matches[1]) && $matches[1] != 'System') {
                                    $apps[] = $matches[1];
                                }
                            }
                        }
                    }
                }
            }
        }
        return $apps;
    }

    /**
     * 安装APP
     *
     * @param $app
     */
    public function installApp($app)
    {
        $class = '\\Be\\Mf\\App\\' . $app . '\\Installer';
        if (class_exists($class)) {
            /**
             * @var \Be\Mf\App\System\Installer $installer
             */
            $installer = new $class();
            $installer->install();

            $property = Be::getProperty('App.' . $app);

            $tuple = Be::newTuple('system_app');
            $tuple->loadBy('name', $app);
            $tuple->name = $app;
            $tuple->label = $property->getLabel();
            $tuple->icon = $property->getIcon();
            $tuple->install_time = date('Y-m-d H:i:s');
            $tuple->update_time = date('Y-m-d H:i:s');
            $tuple->save();

        }
    }

    /**
     * 卸载APP
     *
     * @param $app
     */
    public function uninstallApp($app)
    {
        $class = '\\Be\\Mf\\App\\' . $app . '\\UnInstaller';
        if (class_exists($class)) {
            /**
             * @var \Be\Mf\App\System\UnInstaller $unInstaller
             */
            $unInstaller = new $class();
            $unInstaller->uninstall();

            $tuple = Be::newTuple('system_app');
            $tuple->loadBy('name', $app);
            $tuple->delete();
        }
    }


}
