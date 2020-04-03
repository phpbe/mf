<?php

namespace Be\Plugin\Lists\Toolbar;

use Be\Plugin\Lists\Item;

/**
 * 工具栏驱动
 */
abstract class ToolbarItem extends Item
{

    /**
     * 构造函数
     *
     * @param array $params 注解参数
     */
    public function __construct($params = array())
    {
        parent::__construct($params);
    }


}
