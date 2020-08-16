<?php

namespace Be\Plugin\Curd\FieldItem;

use Be\Plugin\Curd\Item;
use Be\System\Be;

/**
 * 字段驱动
 */
abstract class FieldItem extends Item
{

    public $export = 1; // 是否可导出
    public $exportValue = null; // 控制导出的值，默认取 value

    /**
     * 构造函数
     *
     * @param array $params 参数
     */
    public function __construct($params = [])
    {
        unset($params['value']);

        parent::__construct($params);

        if (isset($params['export'])) {
            $export = $params['export'];
            if (is_callable($export)) {
                $this->export = $export();
            } else {
                $this->export = $export;
            }
        }

        /*
        if (isset($params['exportValue'])) {
            $exportValue = $params['exportValue'];
            if (is_callable($exportValue)) {
                $this->exportValue = $exportValue($tuple);
            } else {
                $this->exportValue = $exportValue;
            }
        }
        */

        if (!isset($this->ui['table-column']['prop'])) {
            $this->ui['table-column']['prop'] = $this->name;
        }

        if (!isset($this->ui['table-column']['label'])) {
            $this->ui['table-column']['label'] = $this->label;
        }

        if (!isset($this->ui['table-column']['sortable']) && isset($params['sortable']) && $params['sortable']) {
            $this->ui['table-column']['sortable'] = 'custom';
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


    /**
     * 获取 vue data
     *
     * @return false | array
     */
    public function getVueData()
    {
        if (!$this->url) return false;

        $vueData = [
            'field' => [
                $this->name => [
                    'url' => $this->url,
                    'target' => $this->target,
                    'postData' => $this->postData,
                ]
            ]
        ];

        if ($this->target == 'dialog') {
            $vueData['field'][$this->name]['dialog'] = $this->dialog;
        } elseif ($this->target == 'drawer') {
            $vueData['field'][$this->name]['drawer'] = $this->drawer;
        }

        return $vueData;
    }


    /**
     * 获取 vue 方法
     *
     * @return false | array
     */
    public function getVueMethods()
    {
        return [
            'fieldClick' => 'function (name, row) {
                var option = this.field[name];
                this.fieldAction(name, option, row);
            }'
        ];
    }

}
