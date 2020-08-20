<?php

namespace Be\Plugin\Curd\SearchItem;

use Be\Plugin\Curd\Item;
use Be\System\Be;

/**
 * 搜索项驱动
 */
abstract class SearchItem extends Item
{

    public $table = null; // 表名
    public $newValue = null; // 新值

    /**
     * 构造函数
     *
     * @param array $params 参数
     */
    public function __construct($params = [])
    {
        parent::__construct($params);

        if (isset($params['table'])) {
            $table = $params['table'];
            if (is_callable($table)) {
                $this->table = $table();
            } else {
                $this->table = $table;
            }
        }

        if (!isset($this->ui['form-item']['label'])) {
            $this->ui['form-item']['label'] = htmlspecialchars($this->label);
        }
    }

    /**
     * 提交处理
     *
     * @param $data
     * @throws \Exception
     */
    public function submit($data)
    {
        if (isset($data[$this->name]) && $data[$this->name] != '') {
            $this->newValue = $data[$this->name];
        }
    }

    /**
     * 查询SQL
     *
     * @param string $dbName
     * @return string
     */
    public function buildSql($dbName = 'master')
    {
        if ($this->newValue !== null) {

            $field = null;
            if ($this->table === null) {
                $field = $this->name;
            } else {
                $field = $this->table .'.' . $this->name;
            }

            $db = Be::getDb($dbName);
            return $db->quoteKey($field) . ' LIKE ' . $db->quoteValue($this->newValue);
        }

        return '';
    }

}
