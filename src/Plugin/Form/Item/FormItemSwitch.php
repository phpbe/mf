<?php

namespace Be\Plugin\Form\Item;

/**
 * 表单项 开关
 */
class FormItemSwitch extends FormItem
{

    public $valueType = 'int';

    /**
     * 构造函数
     *
     * @param array $params 参数
     */
    public function __construct($params = [])
    {
        parent::__construct($params);

        if (!$this->value) {
            $this->value = '0';
        } else {
            $this->value = (string)$this->value;
        }

        if ($this->disabled) {
            if (!isset($this->ui['switch']['disabled'])) {
                $this->ui['switch']['disabled'] = 'true';
            }
        }

        if (!isset($this->ui['switch']['active-value'])) {
            $this->ui['switch']['active-value'] = 1;
        }

        if (!isset($this->ui['switch']['inactive-value'])) {
            $this->ui['switch']['inactive-value'] = 0;
        }

        $this->ui['switch']['v-model'] = 'formData.' . $this->name;
    }

    /**
     * 获取html内容ß
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

        $html .= '<el-switch';
        if (isset($this->ui['switch'])) {
            foreach ($this->ui['switch'] as $k => $v) {
                if ($v === null) {
                    $html .= ' ' . $k;
                } else {
                    $html .= ' ' . $k . '="' . $v . '"';
                }
            }
        }
        $html .= '>';
        $html .= '</el-switch>';

        $html .= '</el-form-item>';
        return $html;
    }


}
