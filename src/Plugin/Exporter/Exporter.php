<?php

namespace Be\Plugin;


use Be\System\Be;
use Be\System\Request;
use Be\System\Response;
use Be\System\Exception\RuntimeException;

/**
 * 导出器
 *
 * Class Exporter
 * @package Be\Plugin
 */
class Exporter
{

    protected $setting = null;

    public function execute($setting = [])
    {
        if (!isset($setting['db'])) {
            $setting['db'] = 'master';
        }

        $this->setting = $setting;

        $task = Request::request('task', 'lists');
        if (isset($this->setting[$task]) && method_exists($this, $task)) {
            $this->$task();
        }
    }


    public static function exportFromSql($config) {

        $dbName = 'master';
        if (isset($config['db'])) {
            $dbName = $config['db'];
        }

        Be::getDb($dbName)->startTransaction();
        try {

            $driver = null;
            switch ($config['driver']) {
                case 'csv':
                    $driver = new \Be\Plugin\Exporter\Csv();
                    break;
                case 'excel':
                    $driver = new \Be\Plugin\Exporter\Excel();
                    break;
                default:
                    throw new RuntimeException('不支持的导出驱动程序！');
            }

            unset($config['driver']);

            $driver->config($config);

            Be::getDb($dbName)->commit();
        } catch (\Exception $e) {
            Be::getDb($dbName)->rollback();
            Response::error($e->getMessage());
        }

    }


    public static function exportFromArrays($config, $header, $arrays) {

        try {
            $driver = null;
            switch ($config['driver']) {
                case 'csv':
                    $driver = new \Be\Plugin\Exporter\Csv();
                    break;
                case 'excel':
                    $driver = new \Be\Plugin\Exporter\Excel();
                    break;
                default:
                    throw new RuntimeException('不支持的导出驱动程序！');
            }

            unset($config['driver']);

            $driver->config($config);

            $driver->setHeader($header);
            $driver->addRows($arrays);

        } catch (\Exception $e) {
            Response::error($e->getMessage());
        }

    }


}