<?php

namespace Be\Plugin\Lists\Toolbar;


/**
 * 图票 工具栏
 */
class ToolbarItemIcon extends ToolbarItem
{



    /**
     * 编辑
     *
     * @return string | array
     */
    public function getHtml()
    {
        $html = '<a-icon';
        if (isset($this->ui['icon'])) {
            foreach ($this->ui['icon'] as $k => $v) {
                if ($v === null) {
                    $html .= ' ' . $k;
                } else {
                    $html .= ' ' . $k . '="' . $v . '"';
                }
            }
        }
        $html .= '>';
        $html .= '</a-icon>';

        return $html;
    }
}
