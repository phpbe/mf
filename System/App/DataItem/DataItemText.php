<?php

namespace Be\System\App\DataItem;

/**
 * 应用配置项 多行文本
 */
class DataItemText extends Driver
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

        if (!isset($this->ui['textarea'][':autosize'])) {
            $this->ui['textarea'][':autosize'] = '{minRows:3,maxRows:10}';
        }

        if (!isset($this->ui['textarea']['v-decorator'])) {
            $this->ui['textarea']['v-decorator'] = '[\''.$name.'\']';
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
        foreach ($this->ui['form-item'] as $k => $v) {
            if ($v === null) {
                $html .= ' '.$k;
            } else {
                $html .= ' '.$k.'="' . $v . '"';
            }
        }
        $html .= '>';

        $html .= '<a-textarea';
        if (isset($this->ui['textarea'])) {
            foreach ($this->ui['textarea'] as $k => $v) {
                if ($v === null) {
                    $html .= ' ' . $k;
                } else {
                    $html .= ' ' . $k . '="' . $v . '"';
                }
            }
        }
        $html .= '>';
        $html .= '</a-textarea>';

        $html .= '</a-form-item>';
        return $html;
    }

}
