<?php

namespace Be\Plugin\Curd\SearchItem;
use Be\System\Be;


/**
 * 搜索项 月份范围选择器
 */
class SearchItemDatePickerMonthRange extends SearchItem
{

    /**
     * 构造函数
     *
     * @param array $params 参数
     * @param object $tuple 行数据
     */
    public function __construct($params = [], $tuple = null)
    {
        parent::__construct($params, $tuple);


        if (!isset($this->ui['date-picker']['range-separator'])) {
            $this->ui['date-picker']['range-separator'] = '至';
        }

        if (!isset($this->ui['date-picker']['start-placeholder'])) {
            $this->ui['date-picker']['start-placeholder'] = '开始月份';
        }

        if (!isset($this->ui['date-picker']['end-placeholder'])) {
            $this->ui['date-picker']['end-placeholder'] = '结束月份';
        }

        if (!isset($this->ui['date-picker']['value-format'])) {
            $this->ui['date-picker']['value-format'] = 'yyyy-MM';
        }

        $this->ui['date-picker']['type'] = 'monthrange';
        $this->ui['date-picker']['v-model'] = 'searchForm.' . $this->name;
    }

    /**
     * 获取html内容
     *
     * @return string | array
     */
    public function getHtml()
    {
        $html = '<el-form-item';
        foreach ($this->ui['form-item'] as $k => $v) {
            if ($v === null) {
                $html .= ' '.$k;
            } else {
                $html .= ' '.$k.'="' . $v . '"';
            }
        }
        $html .= '>';

        $html .= '<el-date-picker';
        if (isset($this->ui['date-picker'])) {
            foreach ($this->ui['date-picker'] as $k => $v) {
                if ($v === null) {
                    $html .= ' ' . $k;
                } else {
                    $html .= ' ' . $k . '="' . $v . '"';
                }
            }
        }
        $html .= '>';
        $html .= '</el-date-picker>';
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
                $field = $this->table .'.' . $this->name;
            }

            $db = Be::getDb($dbName);
            $sql = $db->quoteKey($field) . ' >= ' . $db->quoteValue($this->newValue[0]);
            $sql .= ' AND ';
            $sql .= $db->quoteKey($field) . ' < ' . $db->quoteValue($this->newValue[1]);
            return $sql;
        }

        return '';
    }

}
