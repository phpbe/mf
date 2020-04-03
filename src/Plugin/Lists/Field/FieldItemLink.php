<?php

namespace Be\Plugin\Lists\Field;


/**
 * 搜索项 布尔值
 */
class FieldItemLink extends FieldItem
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
        $html .= $this->value;
        $html .= '</a>';

        return $html;
    }
}
