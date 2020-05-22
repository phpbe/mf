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
        return htmlspecialchars($this->value);
    }

}
