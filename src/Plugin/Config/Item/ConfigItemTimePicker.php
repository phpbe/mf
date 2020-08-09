<?php

namespace Be\Plugin\Config\Item;

use Be\System\Annotation\BeConfigItem;

/**
 * 配置项 时间选择器
 */
class ConfigItemTimePicker extends ConfigItem
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
