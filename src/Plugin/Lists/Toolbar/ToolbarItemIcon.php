<?php

namespace Be\Plugin\Lists\Toolbar;


/**
 * 工具栏 图标
 */
class ToolbarItemIcon extends ToolbarItem
{



    /**
     * 获取html内容
     *
     * @return string | array
     */
    public function getHtml()
    {

        $this->ui['icon']['type'] = $this->value;

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
