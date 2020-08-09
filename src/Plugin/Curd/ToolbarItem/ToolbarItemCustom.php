<?php

namespace Be\Plugin\Curd\ToolbarItem;


/**
 * 工具栏 自定义
 */
class ToolbarItemCustom extends ToolbarItem
{

    /**
     * 获取html内容
     *
     * @return string
     */
    public function getHtml()
    {
        return $this->value;
    }

}
