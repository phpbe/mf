<?php

namespace Be\Plugin\Importer;


use Be\System\Be;
use Be\System\Exception\RuntimeException;

abstract class Driver
{

    /**
     * @var array
     *
     * $config = [
     *      'db' => 'master',
     *      'table' => 'user',
     *      'field' => [
     *          'items' => [
     *              [
     *                  'name' => 'name',  // 数据库字段
     *                  'label' => '表头',
     *                  'index' => 0, // 无表头模式，第几列, 与 label 项二选一，但不能在单个导入功能中混用
     *                  'type' => 'string', // 类型： string / number / datetime / date
     *                  'required' => '1',  // 是否必填
     *                  'check' => function($row) {
     *
     *                  }, // 睚定义校验，校验不通过时可抛异常
     *                  'value' => function($row) {
     *                      return '';
     *                  }, // 格式化
     *              ]
     *          ]
     *      ]
     *  ]
     */

    protected $config = null;

    public function config($config)
    {
        $this->config = $config;
    }


    /**
     * 导入文件
     *
     * @param string $file 文件路径
     * @throws \Exception
     */
    public function import($file)
    {
        $dbName = 'master';
        if (isset($this->config['db'])) {
            $dbName = $this->config['db'];
        }

        $db = Be::getDb($dbName);
        $db->startTransaction();
        try {

            if (!isset($this->config['table'])) {
                throw new RuntimeException('未设置要导入的表名！');
            }
            $table = $this->config['table'];

            $rows = $this->process($file);
            foreach ($rows as $row) {
                $db->insert($table, $row);
            }

            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    /**
     * 处理导入文件
     *
     * @param string $file 文件路径
     * @return \Generator
     */
    public function process($file)
    {
        yield [];
    }


}