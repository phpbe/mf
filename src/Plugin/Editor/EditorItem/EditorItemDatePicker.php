<?php

namespace Be\Plugin\Editor\EditorItem;


/**
 * 搜索项 日期选择器
 */
class EditorItemDatePicker extends EditorItem
{

    /**
     * 获取html内容
     *
     * @return string | array
     */
    public function getHtml()
    {
        if (!isset($this->ui['date-picker']['v-decorator'])) {
            $this->ui['date-picker']['v-decorator'] = '[\''.$this->name.'\']';
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

        $html .= '<a-date-picker';
        if (isset($this->ui['date-picker'])) {
            foreach ($this->ui['date-picker'] as $k => $v) {
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
