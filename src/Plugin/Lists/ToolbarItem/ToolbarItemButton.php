<?php

namespace Be\Plugin\Lists\ToolbarItem;


/**
 * 工具栏 按钮
 */
class ToolbarItemButton extends ToolbarItem
{



    /**
     * 获取html内容
     *
     * @return string | array
     */
    public function getHtml()
    {
        $html = '<a-button';
        if (isset($this->ui['button'])) {
            foreach ($this->ui['button'] as $k => $v) {
                if ($v === null) {
                    $html .= ' ' . $k;
                } else {
                    $html .= ' ' . $k . '="' . $v . '"';
                }
            }
        }
        $html .= '>';
        $html .= $this->value;
        $html .= '</a-button>';

        return $html;
    }
}
