<?php

namespace Be\Plugin\Lists\SearchItem;


use Be\System\Exception\ServiceException;

/**
 * 搜索项 数字
 */
class SearchItemInputNumber extends SearchItem
{


    /**
     * 获取html内容
     *
     * @return string | array
     */
    public function getHtml()
    {
        if (!isset($this->ui['input-number']['v-decorator'])) {
            $this->ui['input-number']['v-decorator'] = '[\''.$this->name.'\']';
        }

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
        if (isset($data[$this->field])) {
            $newValue =  $data[$this->field];

            if (!is_numeric($newValue)) {
                throw new ServiceException('参数 ' . $this->label . ' (' . $this->field . ') 不是合法的数字');
            }

            $this->newValue = $newValue;
        }

    }


}
