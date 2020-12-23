<?php

namespace Be\Plugin\Form\Item;

/**
 * 表单项 月份范围选择器
 */
class FormItemDatePickerMonthRange extends FormItem
{

    protected $valueType = 'array';

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
            if (!isset($this->ui['disabled'])) {
                $this->ui['disabled'] = 'true';
            }
        }

        if (!isset($this->ui['range-separator'])) {
            $this->ui['range-separator'] = '至';
        }

        if (!isset($this->ui['start-placeholder'])) {
            $this->ui['start-placeholder'] = '开始月份';
        }

        if (!isset($this->ui['end-placeholder'])) {
            $this->ui['end-placeholder'] = '结束月份';
        }

        if (!isset($this->ui['value-format'])) {
            $this->ui['value-format'] = 'yyyy-MM';
        }

        $this->ui['type'] = 'monthrange';
        $this->ui['@change'] = 'formItemDatePickerMonthRange_' . $this->name.'_change';
        $this->ui['v-model'] = 'formItems.' . $this->name.'.value';
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
        foreach ($this->ui as $k => $v) {
            if ($k == 'form-item') {
                continue;
            }

            if ($v === null) {
                $html .= ' ' . $k;
            } else {
                $html .= ' ' . $k . '="' . $v . '"';
            }
        }
        $html .= '>';
        $html .= '</el-date-picker>';
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
            'formItemDatePickerMonthRange_' . $this->name . '_change' => 'function(value) {
                if (value) {
                    this.formData.' . $this->name . ' = JSON.stringify(value);
                } else {
                    this.formData.' . $this->name . ' = "";
                }
            }',
        ];
    }

}
