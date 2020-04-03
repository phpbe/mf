<?php

namespace Be\Plugin\Lists\Toolbar;


/**
 * 链接工具栏
 */
class ToolbarItemLink extends ToolbarItem
{



    /**
     * 编辑
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
        $html .= '</a>';

        return $html;
    }
}
