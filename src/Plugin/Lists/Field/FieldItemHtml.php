<?php

namespace Be\Plugin\Lists\Field;


/**
 * 搜索项 布尔值
 */
class FieldItemHtml extends FieldItem
{



    /**
     * 编辑
     *
     * @return string | array
     */
    public function getHtml()
    {
        return $this->value;
    }
}
