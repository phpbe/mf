<?php

namespace Be\Plugin\Lists\SearchItem;

use Be\Plugin\Lists\Item;
use Be\System\Be;

/**
 * 搜索项驱动
 */
abstract class SearchItem extends Item
{

    /**
     * 构造函数
     *
     * @param array $params 参数
     */
    public function __construct($params = [])
    {

        parent::__construct($params);

        if (!isset($this->ui['form-item'][':label-col'])) {
            $this->ui['form-item'][':label-col'] = '{span:6}';
        }

        if (!isset($this->ui['form-item'][':wrapper-col'])) {
            $this->ui['form-item'][':wrapper-col'] = '{span:18}';
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
        if ($this->newValue) {

            if (isset($this->option['db'])) {
                $db = Be::getDb($this->option['db']);
            } else {
                $db = Be::getDb();
            }

            if (isset($this->option['table'])) {
                $where = $db->quoteKey($this->option['table']) . '.';
            }

            $field = isset($this->option['field']) ? $this->option['field'] : $this->name;

            $where[] =  $db->quoteKey($field) . '=' . $db->quoteValue($this->newValue);
        }

        return $where;
    }

}
