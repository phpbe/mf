<?php

namespace Be\Mf\Plugin\Form\Item;

/**
 * 表单项 浮点数
 */
class FormItemInputNumberFloat extends FormItem
{

    public $valueType = 'float'; // 值类型

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
                $this->ui['form-item'][':rules'] = '[{required: true, message: \'请输入'.$this->label.'\', trigger: \'blur\' }]';
            }
        }

        if ($this->disabled) {
            if (!isset($this->ui['disabled'])) {
                $this->ui['disabled'] = 'true';
            }
        }

        if (!isset($this->ui[':precision'])) {
            $this->ui[':precision'] = '2';
        }

        if (!isset($this->ui[':step'])) {
            $this->ui[':step'] = '0.01';
        }

        $this->ui['v-model'] = 'formData.' . $this->name;
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
        foreach ($this->ui as $k => $v) {
            if ($k == 'form-item') {
                continue;
            }

            if ($v === null) {
                $html .= ' '.$k;
            } else {
                $html .= ' '.$k.'="' . $v . '"';
            }
        }
        $html .= '>';
        $html .= '</el-input-number>';

        $html .= '</el-form-item>';
        return $html;
    }


}
