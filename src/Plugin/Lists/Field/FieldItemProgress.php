<?php

namespace Be\Plugin\Lists\Field;


/**
 * 字段 进度条
 */
class FieldItemProgress extends FieldItem
{


    /**
     * 获取html内容
     *
     * @return string | array
     */
    public function getHtml()
    {
        $html = '<a-progress';
        $html .= ' :percent="' . intval($this->value) . '"';

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
