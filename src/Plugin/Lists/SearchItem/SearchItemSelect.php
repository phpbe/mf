<?php

namespace Be\Plugin\Lists\SearchItem;


/**
 * 搜索项 布尔值
 */
class SearchItemSelect extends SearchItem
{

    /**
     * 获取html内容
     *
     * @return string | array
     */
    public function getHtml()
    {
        if (!isset($this->ui['select']['v-decorator'])) {
            $this->ui['select']['v-decorator'] = '[\''.$this->name.'\']';
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

        $html .= '<el-select';
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
        foreach ($this->keyValues as $k => $v) {
            $html .= '<el-select-option';
            $html .= ' key="' . $k . '"';
            $html .= '>';
            $html .= $v;
            $html .= '</el-select-option>';
        }
        $html .= '</el-select>';

        $html .= '</el-form-item>';
        return $html;
    }

}

