<?php

namespace Be\Plugin\Config\Item;


/**
 * 配置项 自定义
 */
class ConfigItemCustom extends ConfigItem
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
