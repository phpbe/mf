<?php

namespace Be\Plugin\Lists\Field;


/**
 * 字段 文本
 */
class FieldItemText extends FieldItem
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
