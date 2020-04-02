<?php

namespace Be\Plugin\Lists\Search;


/**
 * 搜索项 布尔值
 */
class Select extends Driver
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

        if (!isset($this->ui['select']['v-decorator'])) {
            $this->ui['select']['v-decorator'] = '[\''.$name.'\']';
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

        $html .= '<a-select';
        if (isset($this->ui['select'])) {
            foreach ($this->ui['select'] as $k => $v) {
                if ($v === null) {
                    $html .= ' ' . $k;
                } else {
                    $html .= ' ' . $k . '="' . $v . '"';
                }
            }
        }

        $html .= '>';
        foreach ($this->keyValues as $k => $v) {
            $html .= '<a-select-option';
            $html .= ' key="' . $k . '"';
            $html .= '>';
            $html .= $v;
            $html .= '</a-select-option>';
        }
        $html .= '</a-select>';

        $html .= '</a-form-item>';
        return $html;
    }

}

