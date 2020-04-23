<?php

namespace Be\Plugin\Lists\Operation;


/**
 * 操作项 图标
 */
class OperationItemIcon extends OperationItem
{



    /**
     * 获取HTML内容
     *
     * @return string | array
     */
    public function getHtml()
    {
        $html = '<a-icon';
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
        $html .= '</a-icon>';

        return $html;
    }
}
