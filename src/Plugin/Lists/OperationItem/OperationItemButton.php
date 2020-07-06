<?php

namespace Be\Plugin\Lists\OperationItem;


/**
 * 搜索项 布尔值
 */
class OperationItemButton extends OperationItem
{



    /**
     * 编辑
     *
     * @return string | array
     */
    public function getHtml()
    {
        $html = '<el-button';
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

        return $html;
    }
}
