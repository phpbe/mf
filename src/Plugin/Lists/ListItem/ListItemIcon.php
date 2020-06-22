<?php

namespace Be\Plugin\Lists\ListItem;


/**
 * 字段 图标
 */
class ListItemIcon extends ListItem
{



    /**
     * 获取html内容
     *
     * @return string | array
     */
    public function getHtml()
    {

        $this->ui['icon']['class'] = $this->value;

        $html = '<i';
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
        $html .= '</i>';

        return $html;
    }
}
