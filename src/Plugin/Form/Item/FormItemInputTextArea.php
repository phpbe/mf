<?php

namespace Be\Plugin\Form\Item;

/**
 * 表单项 输入框（多行文本）
 */
class FormItemInputTextArea extends FormItem
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
                $this->ui['form-item'][':rules'] = '[{required: true, message: \'请输入'.$this->label.'\', trigger: \'blur\' }]';
            }
        }

        if ($this->disabled) {
            if (!isset($this->ui['input']['disabled'])) {
                $this->ui['input']['disabled'] = 'true';
            }
        }

        $this->ui['input']['type'] = 'textarea';

        if (!isset($this->ui['input'][':autosize'])) {
            $this->ui['input'][':autosize'] = '{minRows:3,maxRows:10}';
        }

        $this->ui['input']['v-model'] = 'formData.' . $this->name;
    }

    /**
     * 获取html内容ß
     *
     * @return string | array
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

        $html .= '<el-input';
        if (isset($this->ui['input'])) {
            foreach ($this->ui['input'] as $k => $v) {
                if ($v === null) {
                    $html .= ' ' . $k;
                } else {
                    $html .= ' ' . $k . '="' . $v . '"';
                }
            }
        }
        $html .= '>';
        $html .= '</el-input>';

        $html .= '</el-form-item>';
        return $html;
    }


}
