<?php

namespace Be\Plugin\Lists\ListItem;

use Be\Plugin\Lists\Item;
use Be\System\Be;

/**
 * 字段驱动
 */
abstract class ListItem extends Item
{

    protected $exportValue = null; // 导出的

    /**
     * 构造函数
     *
     * @param array $params 参数
     * @param object $tuple 行数据
     */
    public function __construct($params = [], $tuple = null)
    {
        parent::__construct($params, $tuple);

        if (isset($params['exportValue'])) {
            $exportValue = $params['exportValue'];
            if (is_callable($exportValue)) {
                $this->exportValue = $exportValue($tuple);
            } else {
                $this->exportValue = $exportValue;
            }
        }
    }



}
