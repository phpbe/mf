<?php

namespace Be\Plugin\Lists\Toolbar;


/**
 * 工具栏 自定义
 */
class ToolbarItemCustom extends ToolbarItem
{

    /**
     * 获取html内容
     *
     * @return string | array
     */
    public function getHtml()
    {
        return $this->value;
    }

}
