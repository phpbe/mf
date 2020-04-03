<?php

namespace Be\Plugin\Lists\Toolbar;


/**
 * 按钮工具栏
 */
class ToolbarItemButton extends ToolbarItem
{



    /**
     * 编辑
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
        $html .= '</a-button>';

        return $html;
    }
}
