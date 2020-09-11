<?php

namespace Be\Plugin\Form\Item;

/**
 * 表单项 日期选择器
 */
class FormItemDatePicker extends FormItem
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
            if (!isset($this->ui['date-picker']['disabled'])) {
                $this->ui['date-picker']['disabled'] = 'true';
            }
        }

        if (!isset($this->ui['date-picker']['placeholder'])) {
            $this->ui['date-picker']['placeholder'] = '选择日期';
        }

        if (!isset($this->ui['date-picker']['value-format'])) {
            $this->ui['date-picker']['value-format'] = 'yyyy-MM-dd';
        }

        $this->ui['date-picker']['type'] = 'date';
        $this->ui['date-picker']['v-model'] = 'formData.' . $this->name;
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

}
