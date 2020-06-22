<?php

namespace Be\Plugin\Lists\SearchItem;

use Be\System\Be;
use Be\System\Exception\ServiceException;

/**
 * 搜索项 布尔值
 */
class SearchItemRangePicker extends SearchItem
{

    /**
     * 获取html内容
     *
     * @return string | array
     */
    public function getHtml()
    {
        if (!isset($this->ui['range-picker']['v-decorator'])) {
            $this->ui['range-picker']['v-decorator'] = '[\'' . $this->name . '\']';
        }

        $html = '<el-form-item';
        foreach ($this->ui['form-item'] as $k => $v) {
            if ($v === null) {
                $html .= ' ' . $k;
            } else {
                $html .= ' ' . $k . '="' . $v . '"';
            }
        }
        $html .= '>';

        $html .= '<el-range-picker';
        if (isset($this->ui['range-picker'])) {
            foreach ($this->ui['range-picker'] as $k => $v) {
                if ($v === null) {
                    $html .= ' ' . $k;
                } else {
                    $html .= ' ' . $k . '="' . $v . '"';
                }
            }
        }
        $html .= '>';
        $html .= '</el-range-picker>';

        $html .= '</el-form-item>';
        return $html;
    }


    /**
     * 提交处理
     *
     * @param $data
     */
    public function submit($data)
    {
        if (isset($data[$this->field]) && $data[$this->field]) {
            $newValue = explode(',', $data[$this->field]);
            if (count($newValue) == 2) {
                $this->newValue = $newValue;
            }
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

            $field = null;
            if (isset($this->option['table'])) {
                $field = $db->quoteKey($this->option['table']) . '.' . $db->quoteKey($this->field);
            } else {
                $field = $db->quoteKey($this->field);
            }

            $where[] = $field . '>=' . $db->quoteValue($this->newValue[0]);
            $where[] = $field . '<=' . $db->quoteValue($this->newValue[1]);
        }

        return $where;
    }
}
