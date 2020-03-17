<?php

namespace Be\Plugin\Exporter;


class Csv extends Driver
{

    private $handler = null;
    private $index = 0;

    /**
     * 准备导出
     */
    public function start()
    {
        session_write_close();

        if (isset($this->config['time_limit']) && is_numeric($this->config['time_limit'])) {
            set_time_limit($this->config['time_limit']);
        } else {
            set_time_limit(3600);
        }

        if (isset($this->config['memory_limit'])) {
            ini_set('memory_limit', $this->config['memory_limit']);
        } else {
            ini_set('memory_limit', '1g');
        }

        header('Content-Type: application/csv');

        if (isset($this->config['filename'])) {
            header('Content-Disposition: attachment; filename=' . $this->config['filename']);
        } else {
            header('Content-Disposition: attachment; filename=' . date('YmdHis') . '.csv');
        }

        $this->handler = fopen('php://output', 'w') or die("can't open php://output");
        fwrite($this->handler, pack('H*', 'FFFE')); // 写入 BOM 头 UTF-16
    }

    /**
     * 设置表格头
     *
     * @param array $header
     */
    public function setHeader($header = [])
    {
        foreach ($header as &$x) {
            $x = iconv('UTF-8', 'UTF-16LE//IGNORE', $x);
        }

        fputcsv($this->handler, $header);
        $this->index++;
    }

    /**
     * 添加一行数据
     *
     * @param array $row
     */
    public function addRow($row = [])
    {
        foreach ($row as &$x) {
            $x = iconv('UTF-8', 'UTF-16LE//IGNORE', $x);
        }

        fputcsv($this->handler, $row);

        $this->index++;
        if ($this->index % 5000 == 0) {
            ob_flush();
            flush();
        }
    }

    /**
     * 结束输出，收尾
     */
    public function end() {
        fclose($this->handler) or die("can't close php://output");
    }

}