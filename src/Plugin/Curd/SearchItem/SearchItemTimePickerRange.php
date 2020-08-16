<?php

namespace Be\Plugin\Curd\SearchItem;

use Be\System\Be;

/**
 * 搜索项 日期时间范围选择器
 */
class SearchItemTimePickerRange extends SearchItem
{

    /**
     * 构造函数
     *
     * @param array $params 参数
     */
    public function __construct($params = [])
    {
        parent::__construct($params);

        if (!isset($this->ui['time-picker']['range-separator'])) {
            $this->ui['time-picker']['range-separator'] = '至';
        }

        if (!isset($this->ui['time-picker']['start-placeholder'])) {
            $this->ui['time-picker']['start-placeholder'] = '开始时间';
        }

        if (!isset($this->ui['time-picker']['end-placeholder'])) {
            $this->ui['time-picker']['end-placeholder'] = '结束时间';
        }

        if (!isset($this->ui['time-picker']['value-format'])) {
            $this->ui['time-picker']['value-format'] = 'HH:mm:ss';
        }

        $this->ui['time-picker']['is-range'] = null;
        $this->ui['time-picker']['v-model'] = 'formData.' . $this->name;
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

        $html .= '<el-time-picker';
        if (isset($this->ui['time-picker'])) {
            foreach ($this->ui['time-picker'] as $k => $v) {
                if ($v === null) {
                    $html .= ' ' . $k;
                } else {
                    $html .= ' ' . $k . '="' . $v . '"';
                }
            }
        }
        $html .= '>';
        $html .= '</el-time-picker>';
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
            $sql = $db->quoteKey($field) . ' >= ' . $db->quoteValue($this->newValue[0]);
            $sql .= ' AND ';
            $sql .= $db->quoteKey($field) . ' < ' . $db->quoteValue($this->newValue[1]);
            return $sql;
        }

        return '';
    }

}

