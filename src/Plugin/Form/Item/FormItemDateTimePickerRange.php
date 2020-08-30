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
     */
    public function __construct($params = [])
    {
        parent::__construct($params);

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


}

