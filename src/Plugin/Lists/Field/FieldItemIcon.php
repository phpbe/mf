<?php

namespace Be\Plugin\Lists\Field;


/**
 * 搜索项 布尔值
 */
class FieldItemIcon extends FieldItem
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
