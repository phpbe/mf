<?php

namespace Be\Plugin\Lists\Field;


/**
 * 字段 自定义
 */
class FieldItemCustom extends FieldItem
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
