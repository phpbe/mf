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
        if (!isset($this->ui['select']['placeholder'])) {
            $this->ui['select']['placeholder'] = '请选择';
        }

        if (!isset($this->ui['select']['filterable'])) {
            $this->ui['select']['filterable'] = null;
        }

        if (!isset($this->ui['select']['clearable'])) {
            $this->ui['select']['clearable'] = null;
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
        $html .= ' v-model="searchForm.' . $this->name . '"';
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
            $html .= '<el-option';
            $html .= ' value="' . $k . '"';
            $html .= ' label="' . $v . '"';
            $html .= '>';
            $html .= '</el-option>';
        }
        $html .= '</el-select>';

        $html .= '</el-form-item>';
        return $html;
    }

}

