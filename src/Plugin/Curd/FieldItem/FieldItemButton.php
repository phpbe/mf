<?php

namespace Be\Plugin\Curd\FieldItem;


/**
 * 字段 按钮
 */
class FieldItemButton extends FieldItem
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

        $html .= '<el-button';
        if (isset($this->ui['button'])) {
            foreach ($this->ui['button'] as $k => $v) {
                if ($v === null) {
                    $html .= ' ' . $k;
                } else {
                    $html .= ' ' . $k . '="' . $v . '"';
                }
            }
        }
        $html .= '>';
        $html .= '{{scope.row.'.$this->name.'}}';
        $html .= '</el-button>';
        $html .= '</template>';
        $html .= '</el-table-column>';

        return $html;
    }
}
