<?php

namespace Be\Plugin\Lists\OperationItem;

use Be\Plugin\Lists\Item;

/**
 * 字段驱动
 */
abstract class OperationItem extends Item
{

    /**
     * 构造函数
     *
     * @param array $params 参数
     * @param object $tuple 行数据
     */
    public function __construct($params = [], $tuple = null)
    {
        parent::__construct($params, $tuple);

        if (!isset($this->ui['table-column']['prop'])) {
            $this->ui['table-column']['prop'] = $this->name;
        }

        if (!isset($this->ui['table-column']['label'])) {
            $this->ui['table-column']['label'] = $this->label;
        }

        if (!isset($this->ui['table-column']['align'])) {
            $this->ui['table-column']['align'] = 'center';
        }

        if (!isset($this->ui['table-column']['header-align'])) {
            $this->ui['table-column']['header-align'] = $this->ui['table-column']['align'];
        }
    }

}
