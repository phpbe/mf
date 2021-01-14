<?php

namespace Be\Mf\Plugin\Exporter;

use Be\Mf\Plugin\PluginException;

/**
 * 导出器
 *
 * Class Exporter
 * @package Be\Mf\Plugin
 */
class Exporter
{

    private $driver = null;

    public function setDriver($driverName){
        switch ($driverName) {
            case 'csv':
                $this->driver = new \Be\Mf\Plugin\Exporter\Csv();
                break;
            case 'excel':
                $this->driver = new \Be\Mf\Plugin\Exporter\Excel();
                break;
            default:
                throw new PluginException('不支持的导出类型（可选值：csv/excel）！');
        }

        return $this->driver;
    }

    public function __call($name, $arguments)
    {
        if ($this->driver === null) {
            throw new PluginException('请先设置导出类型！');
        }

        return call_user_func_array(array($this->driver, $name), $arguments);
    }

}