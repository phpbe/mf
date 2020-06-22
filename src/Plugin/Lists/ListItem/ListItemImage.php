<?php

namespace Be\Plugin\Lists\ListItem;


/**
 * 字段 图片
 */
class ListItemImage extends ListItem
{

    /**
     * 获取html内容
     *
     * @return string | array
     */
    public function getHtml()
    {
        $html = '<el-image';
        $html .= ' src="' . $this->value . '"';

        if (isset($this->ui['image'])) {
            foreach ($this->ui['image'] as $k => $v) {
                if ($v === null) {
                    $html .= ' ' . $k;
                } else {
                    $html .= ' ' . $k . '="' . $v . '"';
                }
            }
        }
        $html .= '>';
        $html .= '</el-image>';

        return $html;
    }

}
