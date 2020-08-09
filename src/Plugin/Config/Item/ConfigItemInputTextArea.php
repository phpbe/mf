<?php

namespace Be\Plugin\Config\Item;

use Be\System\Annotation\BeConfigItem;

/**
 * 配置项 输入框（多行文本）
 */
class ConfigItemInputTextArea extends ConfigItem
{

    /**
     * 构造函数
     *
     * @param string $name 键名
     * @param mixed $value 值
     * @param BeConfigItem $annotation 注解参数
     */
    public function __construct($name, $value, $annotation)
    {
        parent::__construct($name, $value, $annotation);

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
