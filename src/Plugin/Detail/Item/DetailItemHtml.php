<?php

namespace Be\Mf\Plugin\Detail\Item;


/**
 * 明细 HTML
 */
class DetailItemHtml extends DetailItem
{

    /**
     * 获取html内容
     *
     * @return string
     */
    public function getHtml()
    {
        $html = '<el-form-item';
        foreach ($this->ui['form-item'] as $k => $v) {
            if ($v === null) {
                $html .= ' '.$k;
            } else {
                $html .= ' '.$k.'="' . $v . '"';
            }
        }
        $html .= '>';
        $html .= '<div v-html="'.$this->value.'"></div>';
        $html .= '</el-form-item>';
        return $html;
    }

}
