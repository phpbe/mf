<?php

namespace Be\System\App\DataItem;


use Be\System\Service\ServiceException;

/**
 * 应用配置项 浮点数
 */
class DataItemFloat extends Driver
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

        if ($this->keyValues !== null && is_array($this->keyValues)) {

            if (!isset($this->ui['select']['v-decorator'])) {
                $this->ui['select']['v-decorator'] = '[\''.$name.'\']';
            }

        } else {
            if (!isset($this->ui['input-number'][':precision'])) {
                $this->ui['input-number'][':precision'] = '2';
            }

            if (!isset($this->ui['input-number'][':step'])) {
                $this->ui['input-number'][':step'] = '0.01';
            }

            if (!isset($this->ui['input-number'][':formatter'])) {
                $this->ui['input-number'][':formatter'] = 'value => isNaN(value)||value==\'\'?0:parseFloat(value)';
            }

            if (!isset($this->ui['input-number']['v-decorator'])) {
                $this->ui['input-number']['v-decorator'] = '[\''.$name.'\']';
            }
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

        if ($this->keyValues !== null && is_array($this->keyValues)) {
            $html .= '<a-select';
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
            foreach ($this->keyValues as $key => $value) {
                $html .= '<a-select-option';
                $html .= ' key="' . $key . '"';
                $html .= '>';
                $html .= $value;
                $html .= '</a-select-option>';
            }
            $html .= '</a-select>';
        } else {
            $html .= '<a-input-number ';
            if (isset($this->ui['input-number'])) {
                foreach ($this->ui['input-number'] as $k => $v) {
                    if ($v === null) {
                        $html .= ' '.$k;
                    } else {
                        $html .= ' '.$k.'="' . $v . '"';
                    }
                }
            }
            $html .= '>';
            $html .= '</a-input-number>';
        }

        $html .= '</a-form-item>';
        return $html;
    }

    /**
    /**
     * 提交处理
     *
     * @param $data
     * @throws \Exception
     */
    public function submit($data)
    {
        if (isset($data[$this->name])) {
            $newValue = $data[$this->name];

            if (!is_numeric($newValue)) {
                throw new ServiceException('参数 ' . $this->label . ' (' . $this->name . ') 不是合法的数字');
            }

            $this->newValue = $newValue;
        }
    }


}
