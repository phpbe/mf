<?php

namespace Be\Plugin\Detail\Item;


/**
 * 明细 文本
 */
class DetailItemText extends DetailItem
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
        $html .= $this->value;
        $html .= '</el-form-item>';
        return $html;
    }

}
