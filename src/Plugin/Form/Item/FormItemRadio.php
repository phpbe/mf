<?php

namespace Be\Plugin\Form\Item;

/**
 * 表单项 单选框
 */
class FormItemRadio extends FormItem
{

    /**
     * 构造函数
     *
     * @param array $params 参数
     * @param array $row 数据对象
     */
    public function __construct($params = [], $row = [])
    {
        parent::__construct($params, $row);

        if ($this->required) {
            if (!isset($this->ui['form-item'][':rules'])) {
                $this->ui['form-item'][':rules'] = '[{required: true, message: \'请选择'.$this->label.'\', trigger: \'change\' }]';
            }
        }

        if ($this->disabled) {
            if (!isset($this->ui['radio']['disabled'])) {
                $this->ui['radio']['disabled'] = 'true';
            }
        }

        $this->ui['radio']['v-model'] = 'formData.' . $this->name;
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
                $html .= ' '.$k;
            } else {
                $html .= ' '.$k.'="' . $v . '"';
            }
        }
        $html .= '>';

        foreach ($this->keyValues as $key => $val) {
            $html .= '<el-radio';
            if (isset($this->ui['radio'])) {
                foreach ($this->ui['radio'] as $k => $v) {
                    if ($v === null) {
                        $html .= ' ' . $k;
                    } else {
                        $html .= ' ' . $k . '="' . $v . '"';
                    }
                }
            }

            $html .= ' label="'. $key .'"';
            $html .= '>';
            $html .= $val;
            $html .= '</el-radio>';
        }

        $html .= '</el-form-item>';
        return $html;
    }

}
