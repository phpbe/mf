<?php

namespace Be\Plugin\Lists\ListItem;


/**
 * 字段 链接
 */
class ListItemLink extends ListItem
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
