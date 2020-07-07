<?php

namespace Be\Plugin\Lists\FieldItem;

use Be\Plugin\Lists\Item;
use Be\System\Be;

/**
 * 字段驱动
 */
abstract class FieldItem extends Item
{

    protected $export = 1; // 是否可导出
    protected $exportValue = null; // 控制导出的值，默认取 value

    /**
     * 构造函数
     *
     * @param array $params 参数
     * @param object $tuple 行数据
     */
    public function __construct($params = [], $tuple = null)
    {
        parent::__construct($params, $tuple);

        if (isset($params['export'])) {
            $export = $params['export'];
            if (is_callable($export)) {
                $this->export = $export($tuple);
            } else {
                $this->export = $export;
            }
        }

        if (isset($params['exportValue'])) {
            $exportValue = $params['exportValue'];
            if (is_callable($exportValue)) {
                $this->exportValue = $exportValue($tuple);
            } else {
                $this->exportValue = $exportValue;
            }
        }

        if (!isset($this->ui['table-column']['prop'])) {
            $this->ui['table-column']['prop'] = $this->name;
        }

        if (!isset($this->ui['table-column']['label'])) {
            $this->ui['table-column']['label'] = $this->label;
        }

        if (!isset($this->ui['table-column']['width']) && isset($params['width'])) {
            $this->ui['table-column']['width'] = $params['width'];
        }

        if (!isset($this->ui['table-column']['align'])) {
            if (isset($params['align'])) {
                $this->ui['table-column']['align'] = $params['align'];
            } else {
                $this->ui['table-column']['align'] = 'center';
            }
        }

        if (!isset($this->ui['table-column']['header-align'])) {
            if (isset($params['header-align'])) {
                $this->ui['table-column']['header-align'] = $params['header-align'];
            } else {
                $this->ui['table-column']['header-align'] = $this->ui['table-column']['align'];
            }
        }

    }



}
