<?php

namespace Be\Plugin\Curd\SearchItem;

use Be\System\Exception\ServiceException;

/**
 * 搜索项 浮点数
 */
class SearchItemInputNumberFloat extends SearchItem
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

        if (!isset($this->ui['input-number'][':precision'])) {
            $this->ui['input-number'][':precision'] = '2';
        }

        if (!isset($this->ui['input-number'][':step'])) {
            $this->ui['input-number'][':step'] = '0.01';
        }

        if (!isset($this->ui['input-number'][':formatter'])) {
            $this->ui['input-number'][':formatter'] = 'value => isNaN(value)||value==\'\'?0:parseFloat(value)';
        }

        $this->ui['input-number']['v-model'] = 'formData.' . $this->name;
    }

    /**
     * 获取html内容
     *
     * @return string
     */
    public function getEditHtml()
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

        $html .= '<el-input-number ';
        if (isset($this->ui['input-number'])) {
            foreach ($this->ui['input-number'] as $k => $v) {
                if ($v === null) {
                    $html .= ' '.$k;
                } else {
                    $html .= ' '.$k.'="' . $v . '"';
                }
            }
        }
        $html .= '>';
        $html .= '</el-input-number>';

        $html .= '</el-form-item>';
        return $html;
    }

    /**
    /**
     * 提交处理
     *
     * @param $data
     * @throws \Exception
     */
    public function submit($data)
    {
        if (isset($data[$this->name])) {
            $newValue = $data[$this->name];

            if (!is_numeric($newValue)) {
                throw new ServiceException('参数 ' . $this->label . ' (' . $this->name . ') 不是合法的数字');
            }

            $this->newValue = (float) $newValue;
        }
    }



}
