<?php

namespace Be\System\App\DataItem;

/**
 * 数据配置项 日期时间
 */
class DataItemTime extends Driver
{

    /**
     * 构造函数
     *
     * @param string $name 键名
     * @param mixed $value 值
     * @param array $params 参数
     */
    public function __construct($name, $value, $params = array())
    {
        parent::__construct($name, $value, $params);

        if (!isset($params['placeholder'])) {
            $this->ui['placeholder'] = '选择时间';
        }

        if (!isset($this->ui['value-format'])) {
            $this->ui['value-format'] = 'HH:mm:ss';
        }
    }

    /**
     * 编辑
     *
     * @return string | array
     */
    public function getEditHtml()
    {
        $html = '<a-form-item';
        $html .= ' label="'.$this->label.'">';
        $html .= '<a-time-select';
        $html .= ' v-model="formData.' . $this->name . '"';
        foreach ($this->ui as $key => $val) {
            if ($val === null) {
                $html .= ' '.$key;
            } else {
                $html .= ' '.$key.'="' . $val . '"';
            }
        }
        $html .= '>';
        $html .= '</a-time-select>';
        $html .= '</a-form-item>';
        return $html;
    }

}
