<?php

namespace Be\Plugin\Lists\Field;


/**
 * 字段 标签
 */
class FieldItemTag extends FieldItem
{

    /**
     * 获取html内容
     *
     * @return string
     */
    public function getHtml()
    {
        $html = '<a-tag';
        if (isset($this->ui['tag'])) {
            foreach ($this->ui['tag'] as $k => $v) {
                if ($v === null) {
                    $html .= ' ' . $k;
                } else {
                    $html .= ' ' . $k . '="' . $v . '"';
                }
            }
        }
        $html .= '>';
        $html .= $this->value;
        $html .= '</a-tag>';

        return $html;
    }
}
