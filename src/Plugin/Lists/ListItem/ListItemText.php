<?php

namespace Be\Plugin\Lists\ListItem;


/**
 * 字段 文本
 */
class ListItemText extends ListItem
{


    /**
     * 获取html内容
     *
     * @return string | array
     */
    public function getHtml()
    {
        $html = '<el-table-column';
        if (isset($this->ui['table-column'])) {
            foreach ($this->ui['table-column'] as $k => $v) {
                if ($v === null) {
                    $html .= ' ' . $k;
                } else {
                    $html .= ' ' . $k . '="' . $v . '"';
                }
            }
        }
        $html .= '>';
        $html .= '<template slot-scope="scope">';
        $html .= '{{scope.row.'.$this->name.'}}';
        $html .= '</template>';
        $html .= '</el-table-column>';

        return $html;
    }


}
