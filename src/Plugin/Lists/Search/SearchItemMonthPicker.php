<?php

namespace Be\Plugin\Lists\Search;

use Be\System\Exception\ServiceException;

/**
 * 搜索项 布尔值
 */
class SearchItemMonthPicker extends SearchItem
{


    /**
     * 构造函数
     *
     * @param array $params 参数
     */
    public function __construct($params = array())
    {
        parent::__construct($params);

        if (!isset($this->ui['month-picker']['v-decorator'])) {
            $this->ui['month-picker']['v-decorator'] = '[\''.$this->name.'\']';
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

        $html .= '<a-month-picker';
        if (isset($this->ui['month-picker'])) {
            foreach ($this->ui['month-picker'] as $k => $v) {
                if ($v === null) {
                    $html .= ' ' . $k;
                } else {
                    $html .= ' ' . $k . '="' . $v . '"';
                }
            }
        }
        $html .= '>';
        $html .= '</a-date-picker>';

        $html .= '</a-form-item>';
        return $html;
    }


}
