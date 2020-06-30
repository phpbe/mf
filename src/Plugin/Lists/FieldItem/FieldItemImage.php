<?php

namespace Be\Plugin\Lists\FieldItem;


/**
 * 字段 图片
 */
class FieldItemImage extends FieldItem
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
        $html .= '<el-image';
        $html .= ' src="{{scope.row.'.$this->name.'}}"';

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
        $html .= '</template>';
        $html .= '</el-table-column>';

        return $html;
    }

}
