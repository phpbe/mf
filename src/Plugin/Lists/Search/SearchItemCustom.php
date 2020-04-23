<?php

namespace Be\Plugin\Lists\Search;


/**
 * 搜索项 自定义
 */
class SearchItemCustom extends SearchItem
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
