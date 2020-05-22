<?php

namespace Be\Plugin\Lists\ListItem;


/**
 * 字段 自定义
 */
class ListItemCustom extends ListItem
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
