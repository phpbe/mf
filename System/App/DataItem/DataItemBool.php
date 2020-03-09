<?php

namespace Be\System\App\DataItem;


use Be\System\Service\ServiceException;


/**
 * 应用配置项 布尔值
 */
class DataItemBool extends Driver
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

        if (!isset($this->ui['switch']['v-decorator'])) {
            $this->ui['switch']['v-decorator'] = '[\''.$name.'\']';
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
        $html .= '<a-switch';
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
        $html .= '</a-switch>';

        $html .= '</a-form-item>';
        return $html;
    }

    /**
     * 明细
     *
     * @return string
     */
    public function getDetailHtml()
    {
        if ($this->keyValues !== null && is_array($this->keyValues) && isset($this->keyValues[$this->value])) {
            return $this->keyValues[$this->value];
        }

        $html = '<a-switch';
        $html .= ' value="' . $this->value . '"';
        $html .= ' disabled';
        $html .= '>';
        $html .= '</a-switch>';
        return $html;
    }


    /**
     * 提交处理
     *
     * @param $data
     */
    public function submit($data)
    {
        if (isset($data[$this->name])) {
            $this->newValue = $data[$this->name];
        }
    }

}
