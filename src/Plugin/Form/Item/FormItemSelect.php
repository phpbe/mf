<?php

namespace Be\Plugin\Form\Item;

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

        if ($this->required) {
            if (!isset($this->ui['form-item'][':rules'])) {
                $this->ui['form-item'][':rules'] = '[{required: true, message: \'请选择' . $this->label . '\', trigger: \'change\' }]';
            }
        } else {
            if (!isset($this->ui['select']['clearable'])) {
                $this->ui['select']['clearable'] = null;
            }
        }

        if ($this->disabled) {
            if (!isset($this->ui['select']['disabled'])) {
                $this->ui['select']['disabled'] = 'true';
            }
        }

        if (!isset($this->ui['select']['placeholder'])) {
            $this->ui['select']['placeholder'] = '请选择';
        }

        if (!isset($this->ui['select']['filterable'])) {
            $this->ui['select']['filterable'] = null;
        }

        $this->ui['select']['v-model'] = 'formData.' . $this->name;
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
        $html .= '<el-option v-for="(v, k) in formItems.' . $this->name . '.keyValues" :key="k" :label="v" :value="k"></el-option>';
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

