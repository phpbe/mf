<?php

namespace Be\Plugin\Config\Item;

use Be\System\Annotation\BeConfigItem;

/**
 * 配置项 日期时间范围选择器
 */
class ConfigItemTimePickerRange extends ConfigItem
{

    public $valueType = 'array';

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

}

