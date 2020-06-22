<?php

namespace Be\Plugin\Lists\ToolbarItem;


/**
 * 工具栏 链接
 */
class ToolbarItemLink extends ToolbarItem
{



    /**
     * 获取html内容
     *
     * @return string | array
     */
    public function getHtml()
    {
        $html = '<a';
        if (isset($this->ui['link'])) {
            foreach ($this->ui['link'] as $k => $v) {
                if ($v === null) {
                    $html .= ' ' . $k;
                } else {
                    $html .= ' ' . $k . '="' . $v . '"';
                }
            }
        }
        $html .= '>';
        $html .= $this->value;
        $html .= '</a>';

        return $html;
    }
}