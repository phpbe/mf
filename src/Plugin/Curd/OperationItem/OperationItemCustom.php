<?php

namespace Be\Plugin\Curd\OperationItem;


/**
 * 操作项 自定义
 */
class OperationItemCustom extends OperationItem
{



    /**
     * 获取HTML内容
     *
     * @return string
     */
    public function getHtml()
    {
        $html = '{{scope.row.'.$this->name.'}}';
        return $html;
    }
}
