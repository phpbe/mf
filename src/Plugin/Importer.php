<?php

namespace Be\Plugin;

use Be\System\Be;
use Be\System\Request;
use Be\System\Response;
use Be\System\RuntimeException;

/**
 * 导入器
 *
 * Class Importer
 * @package Be\Plugin
 */
class Importer
{

    public static function import($config)
    {

        if (Request::isPost()) {

            $dbName = 'master';
            if (isset($config['db'])) {
                $dbName = $config['db'];
            }

            Be::getDb($dbName)->startTransaction();
            try {

                $driver = null;
                switch ($config['driver']) {
                    case 'csv':
                        $driver = new \Be\Plugin\Importer\Csv();
                        break;
                    case 'excel':
                        $driver = new \Be\Plugin\Importer\Excel();
                        break;
                    default:
                        throw new RuntimeException('不支持的导出驱动程序！');
                }

                unset($config['driver']);

                $driver->import($config);

                Be::getDb($dbName)->commit();
            } catch (\Exception $e) {
                Be::getDb($dbName)->rollback();
                Response::error($e->getMessage());
            }

            Response::success('创建成功！');

        } else {
            Response::setTitle($config['name'] . '：创建');
            Response::display('Plugin', 'Importer.import');
        }

    }

}