<?php

namespace Be\Plugin\Form\Item;

/**
 * 表单项 时间选择器
 */
class FormItemTimePicker extends FormItem
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
            if (!isset($this->ui['time-picker']['disabled'])) {
                $this->ui['time-picker']['disabled'] = 'true';
            }
        }

        if (!isset($this->ui['time-picker']['placeholder'])) {
            $this->ui['time-picker']['placeholder'] = '选择时间';
        }

        if (!isset($this->ui['time-picker']['value-format'])) {
            $this->ui['time-picker']['value-format'] = 'HH:mm:ss';
        }

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
                $html .= ' '.$k;
            } else {
                $html .= ' '.$k.'="' . $v . '"';
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

}
