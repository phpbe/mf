<?php

namespace Be\Plugin\Lists\Search;

use Be\System\Be;
use Be\System\Exception\ServiceException;

/**
 * 搜索项 布尔值
 */
class RangePicker extends Driver
{


    /**
     * 构造函数
     *
     * @param string $name 键名
     * @param mixed $value 值
     * @param array $params 参数
     */
    public function __construct($name, $value, $params = array())
    {
        parent::__construct($name, $value, $params);

        if (!isset($this->ui['range-picker']['v-decorator'])) {
            $this->ui['range-picker']['v-decorator'] = '[\''.$name.'\']';
        }
    }

    /**
     * 编辑
     *
     * @return string | array
     */
    public function getEditHtml()
    {
        $html = '<a-form-item';
        foreach ($this->ui['form-item'] as $k => $v) {
            if ($v === null) {
                $html .= ' '.$k;
            } else {
                $html .= ' '.$k.'="' . $v . '"';
            }
        }
        $html .= '>';

        $html .= '<a-range-picker';
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
        $html .= '</a-range-picker>';

        $html .= '</a-form-item>';
        return $html;
    }


    /**
     * 提交处理
     *
     * @param $data
     */
    public function submit($data)
    {
        if (isset($data[$this->name]) && $data[$this->name]) {
            $newValue = explode(',', $data[$this->name]);
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

            $where[] =  $db->quoteKey($field) . '>=' . $db->quoteValue($this->newValue[0]);
            $where[] =  $db->quoteKey($field) . '<=' . $db->quoteValue($this->newValue[1]);
        }

        return $where;
    }
}
