<?php

namespace Be\Plugin\Lists\Field;


/**
 * 搜索项 布尔值
 */
class FieldItemProgress extends FieldItem
{


    /**
     * 编辑
     *
     * @return string | array
     */
    public function getHtml()
    {
        $html = '<a-progress';
        $html .= ' :percent="' . $this->value . '"';

        if (isset($this->ui['progress'])) {
            foreach ($this->ui['progress'] as $k => $v) {
                if ($v === null) {
                    $html .= ' ' . $k;
                } else {
                    $html .= ' ' . $k . '="' . $v . '"';
                }
            }
        }
        $html .= '>';
        $html .= '</a-progress>';

        return $html;
    }
}
