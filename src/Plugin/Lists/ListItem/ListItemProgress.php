<?php

namespace Be\Plugin\Lists\ListItem;


/**
 * 字段 进度条
 */
class ListItemProgress extends ListItem
{


    /**
     * 获取html内容
     *
     * @return string | array
     */
    public function getHtml()
    {
        $html = '<el-progress';
        $html .= ' :percentage="' . intval($this->value) . '"';

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
        $html .= '</el-progress>';

        return $html;
    }

}