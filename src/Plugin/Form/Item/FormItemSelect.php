<?php

namespace Be\Mf\Plugin\Form\Item;

/**
 * 表单项 下拉框
 */
class FormItemSelect extends FormItem
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

        if ($this->keyValues) {
            $newKeyValues = [];
            foreach ($this->keyValues as $key => $value) {
                $newKeyValues[] = [
                    'key' => '' . $key,
                    'value' => $value
                ];
            }
            $this->keyValues = $newKeyValues;
        }

        if ($this->required) {
            if (!isset($this->ui['form-item'][':rules'])) {
                $this->ui['form-item'][':rules'] = '[{required: true, message: \'请选择' . $this->label . '\', trigger: \'change\' }]';
            }
        } else {
            if (!isset($this->ui['clearable'])) {
                $this->ui['clearable'] = null;
            }
        }

        if ($this->disabled) {
            if (!isset($this->ui['disabled'])) {
                $this->ui['disabled'] = 'true';
            }
        }

        if (!isset($this->ui['placeholder'])) {
            $this->ui['placeholder'] = '请选择';
        }

        if (!isset($this->ui['filterable'])) {
            $this->ui['filterable'] = null;
        }

        $this->ui['v-model'] = 'formData.' . $this->name;
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

        $html .= '<el-select';
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
        $html .= '<el-option v-for="(item, k) in formItems.' . $this->name . '.keyValues" :key="k" :label="item.value" :value="item.key"></el-option>';
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
                    'keyValues' => $this->keyValues,
                ]
            ]
        ];
    }

}

