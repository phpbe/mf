<?php

namespace Be\Plugin\Lists\Search;

use Be\System\Exception\ServiceException;

/**
 * 搜索项 布尔值
 */
class InputNumberInt extends Driver
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

        if (!isset($this->ui['input-number'][':step'])) {
            $this->ui['input-number'][':step'] = '1';
        }

        if (!isset($this->ui['input-number'][':formatter'])) {
            $this->ui['input-number'][':formatter'] = 'value => isNaN(value)||value==\'\'?0:parseInt(value)';
        }

        if (!isset($this->ui['input-number']['v-decorator'])) {
            $this->ui['input-number']['v-decorator'] = '[\''.$name.'\']';
        }

    }


    /**
     * 编辑
     *
     * @return string | array
     */
    public function getHtml()
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

        $html .= '<a-input-number';
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

        $html .= '</a-form-item>';
        return $html;
    }

    /**
    /**
     * 提交处理
     *
     * @param $data
     * @throws ServiceException
     */
    public function submit($data)
    {
        if (isset($data[$this->name])) {
            $newValue =  $data[$this->name];

            if (!is_numeric($newValue)) {
                throw new ServiceException('参数 ' . $this->label . ' (' . $this->name . ') 不是合法的数字');
            }

            $this->newValue = (int) $newValue;
        }

    }


}
