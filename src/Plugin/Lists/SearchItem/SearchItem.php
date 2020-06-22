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
        if (isset($data[$this->field])) {
            $newValue = $data[$this->field];
            if (!is_array($newValue) && !is_object($newValue)) {
                $newValue =  htmlspecialchars_decode($newValue);
            }
            $this->newValue = $newValue;
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
                $field = $db->quoteKey($this->option['table']) . '.' . $db->quoteKey($this->field);
            } else {
                $field = $db->quoteKey($this->field);
            }

            $where[] =  $field . '=' . $db->quoteValue($this->newValue);
        }

        return $where;
    }

}
