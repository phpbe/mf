<?php

namespace Be\Plugin\Lists\SearchItem;


/**
 * 搜索项 布尔值
 */
class SearchItemMonthPicker extends SearchItem
{

    /**
     * 获取html内容
     *
     * @return string | array
     */
    public function getHtml()
    {
        if (!isset($this->ui['month-picker']['v-decorator'])) {
            $this->ui['month-picker']['v-decorator'] = '[\''.$this->name.'\']';
        }

        $html = '<el-form-item';
        foreach ($this->ui['form-item'] as $k => $v) {
            if ($v === null) {
                $html .= ' '.$k;
            } else {
                $html .= ' '.$k.'="' . $v . '"';
            }
        }
        $html .= '>';

        $html .= '<el-month-picker';
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
        $html .= '</el-date-picker>';

        $html .= '</el-form-item>';
        return $html;
    }


}
