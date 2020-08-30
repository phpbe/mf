<?php

namespace Be\Plugin\Form\Item;


/**
 * 表单项 自定义
 */
class FormItemCustom extends FormItem
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
