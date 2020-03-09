<?php

namespace Be\System\App\DataItem;

/**
 * 应用配置项 整型
 */
class DataItemEmail extends Driver
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

        if ($this->keyValues !== null && is_array($this->keyValues)) {
            if (!isset($this->ui['select']['v-decorator'])) {
                $this->ui['select']['v-decorator'] = '[\''.$name.'\']';
            }
        } else {
            if (!isset($this->ui['input']['v-decorator'])) {
                $this->ui['input']['v-decorator'] = '[\''.$name.'\',{rules: [{type: \'email\', message: \'请输入合法的邮箱!\'}]}]';
            }
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
        if ($this->keyValues !== null && is_array($this->keyValues)) {
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
        } else {
            $html .= '<a-input';
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
            $html .= '</a-input>';
        }

        $html .= '</a-form-item>';
        return $html;
    }

}
