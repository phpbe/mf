<?php

namespace Be\Plugin\Lists\ListItem;


/**
 * 字段 标签
 */
class ListItemTag extends ListItem
{

    /**
     * 获取html内容
     *
     * @return string
     */
    public function getHtml()
    {
        $html = '<el-tag';
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
        $html .= '</el-tag>';

        return $html;
    }
}
