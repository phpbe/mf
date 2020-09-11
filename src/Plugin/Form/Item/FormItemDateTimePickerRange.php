<?php

namespace Be\Plugin\Form\Item;

/**
 * 表单项 日期时间范围选择器
 */
class FormItemDateTimePickerRange extends FormItem
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
            if (!isset($this->ui['date-picker']['disabled'])) {
                $this->ui['date-picker']['disabled'] = 'true';
            }
        }

        if (!isset($this->ui['date-picker']['range-separator'])) {
            $this->ui['date-picker']['range-separator'] = '至';
        }

        if (!isset($this->ui['date-picker']['start-placeholder'])) {
            $this->ui['date-picker']['start-placeholder'] = '开始日期时间';
        }

        if (!isset($this->ui['date-picker']['end-placeholder'])) {
            $this->ui['date-picker']['end-placeholder'] = '结束日期时间';
        }

        if (!isset($this->ui['date-picker']['value-format'])) {
            $this->ui['date-picker']['value-format'] = 'yyyy-MM-dd HH:mm:ss';
        }

        $this->ui['date-picker']['type'] = 'datetimerange';
        $this->ui['date-picker']['@change'] = 'formItemDateTimePickerRange_' . $this->name.'_change';
        $this->ui['date-picker']['v-model'] = 'formItems.' . $this->name.'.range';
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
                    'range' => $this->value,
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
            'formItemDateTimePickerRange_' . $this->name . '_change' => 'function(value) {
                this.formData.' . $this->name . ' = JSON.stringify(value);
            }',
        ];
    }

}

