<?php

namespace Be\Plugin\Exporter;


abstract class Driver
{

    protected $config = null;

    public function config($config)
    {
        $this->config = $config;
    }


    /**
     * 准备导出
     */
    abstract public function start();

    /**
     * 设置表格头
     *
     * @param array $header
     */
    abstract public function setHeader($header = []);


    /**
     * 添加一行数据
     *
     * @param array $row
     */
    abstract public function addRow($row = []);

    /**
     * 添加多行数据
     *
     * @param array $rows
     */
    public function addRows($rows = [])
    {
        foreach ($rows as $row) {
            $this->addRow($row);
        }
    }

    /**
     * 结束输出，收尾
     */
    public function end() {

    }
}