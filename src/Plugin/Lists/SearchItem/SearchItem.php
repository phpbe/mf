<?php

namespace Be\Plugin\Lists\SearchItem;

use Be\Plugin\Lists\Item;
use Be\System\Be;

/**
 * 搜索项驱动
 */
abstract class SearchItem extends Item
{

    protected $newValue = null; // 新值

    /**
     * 构造函数
     *
     * @param array $params 参数
     * @param object $tuple 行数据
     */
    public function __construct($params = [], $tuple = null)
    {
        parent::__construct($params, $tuple);

        if (!isset($this->ui['form-item']['label'])) {
            $this->ui['form-item']['label'] = htmlspecialchars($this->label);
        }

        if ($this->description) {
            if (!isset($this->ui['form-item']['help'])) {
                $this->ui['form-item']['help'] = htmlspecialchars($this->description);
            }
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
        if (isset($data[$this->name]) && $data[$this->name]) {
            $this->newValue = $data[$this->name];
        }
    }

    /**
     * 查询SQL
     *
     * @return string
     */
    public function buildSql()
    {
        $where = [];
        if ($this->newValue !== null) {

            if (isset($this->option['db'])) {
                $db = Be::getDb($this->option['db']);
            } else {
                $db = Be::getDb();
            }

            if (isset($this->option['table'])) {
                $where = $db->quoteKey($this->option['table']) . '.';
            }

            $field = null;
            if (isset($this->option['table'])) {
                $field = $db->quoteKey($this->option['table']) . '.' . $db->quoteKey($this->name);
            } else {
                $field = $db->quoteKey($this->name);
            }

            $where[] =  $field . '=' . $db->quoteValue($this->newValue);
        }

        return $where;
    }

}
