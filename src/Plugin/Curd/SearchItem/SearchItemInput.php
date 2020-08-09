<?php

namespace Be\Plugin\Curd\SearchItem;

use Be\System\Be;


/**
 * 输入框
 */
class SearchItemInput extends SearchItem
{

    public $op = '='; // SQL 操作

    /**
     * 构造函数
     *
     * @param array $params 参数
     * @param object $tuple 行数据
     */
    public function __construct($params = [], $tuple = null)
    {
        parent::__construct($params, $tuple);

        if (isset($params['op'])) {
            $this->op = strtoupper($params['op']);
        }

        $this->ui['input']['v-model'] = 'formData.' . $this->name;
    }

    /**
     * 获取html内容
     *
     * @return string
     */
    public function getHtml()
    {
        $html = '<el-form-item';
        foreach ($this->ui['form-item'] as $k => $v) {
            if ($v === null) {
                $html .= ' ' . $k;
            } else {
                $html .= ' ' . $k . '="' . $v . '"';
            }
        }
        $html .= '>';

        $html .= '<el-input';
        if (isset($this->ui['input'])) {
            foreach ($this->ui['input'] as $k => $v) {
                if ($v === null) {
                    $html .= ' ' . $k;
                } else {
                    $html .= ' ' . $k . '="' . $v . '"';
                }
            }
        }
        $html .= '>';
        $html .= '</el-input>';

        $html .= '</el-form-item>';
        return $html;
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
                $field = $this->table . '.' . $this->name;
            }

            $db = Be::getDb($dbName);
            switch ($this->op) {
                case 'LIKE':
                    return $db->quoteKey($field) . ' LIKE ' . $db->quoteValue($this->newValue);
                case '%LIKE%':
                    return $db->quoteKey($field) . ' LIKE ' . $db->quoteValue('%' . $this->newValue . '%');
                case 'LIKE%':
                    return $db->quoteKey($field) . ' LIKE ' . $db->quoteValue($this->newValue . '%');
                case '%LIKE':
                    return $db->quoteKey($field) . ' LIKE ' . $db->quoteValue('%' . $this->newValue);
                default:
                    return $db->quoteKey($field) . ' ' . $this->op . ' ' . $db->quoteValue($this->newValue);
            }
        }

        return '';
    }

}
