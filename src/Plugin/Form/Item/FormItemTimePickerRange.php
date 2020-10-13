<?php

namespace Be\Plugin\Form\Item;

/**
 * 表单项 日期时间范围选择器
 */
class FormItemTimePickerRange extends FormItem
{

    public $valueType = 'array';

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
        $this->ui['date-picker']['@change'] = 'formItemTimePickerRange_' . $this->name.'_change';
        $this->ui['date-picker']['v-model'] = 'formItems.' . $this->name.'.value';
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
     * 获取 vue data
     *
     * @return false | array
     */
    public function getVueData()
    {
        return [
            'formItems' => [
                $this->name => [
                    'value' => $this->value,
                ]
            ]
        ];
    }

    /**
     * 获取 vue 方法
     *
     * @return false | array
     */
    public function getVueMethods()
    {
        return [
            'formItemTimePickerRange_' . $this->name . '_change' => 'function(value) {
                if (value) {
                    this.formData.' . $this->name . ' = JSON.stringify(value);
                } else {
                    this.formData.' . $this->name . ' = "";
                }
            }',
        ];
    }

}

