<?php

namespace Be\Plugin\Lists\SearchItem;

use Be\System\Be;

/**
 * 搜索项 日期选择器
 */
class SearchItemDateRangePicker extends SearchItem
{

    /**
     * 获取html内容
     *
     * @return string | array
     */
    public function getHtml()
    {
        if (!isset($this->ui['date-picker']['range-separator'])) {
            $this->ui['date-picker']['range-separator'] = '至';
        }

        if (!isset($this->ui['date-picker']['start-placeholder'])) {
            $this->ui['date-picker']['start-placeholder'] = '开始日期';
        }

        if (!isset($this->ui['date-picker']['end-placeholder'])) {
            $this->ui['date-picker']['end-placeholder'] = '结束日期';
        }

        if (!isset($this->ui['date-picker']['value-format'])) {
            $this->ui['date-picker']['value-format'] = 'yyyy-MM-dd';
        }

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
        $html .= ' type="daterange"';
        $html .= ' v-model="searchForm.' . $this->name . '"';
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
            if (isset($this->option['table'])) {
                $field = $this->option['table'] .'.' . $this->name;
            } else {
                $field = $this->name;
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
