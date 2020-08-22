<?php

namespace Be\Plugin\Curd\OperationItem;

use Be\Plugin\Curd\Item;

/**
 * 操作项 驱动
 */
abstract class OperationItem extends Item
{

    /**
     * 获取 vue data
     *
     * @return false | array
     */
    public function getVueData()
    {
        $vueData = [
            'operation' => [
                $this->name => [
                    'url' => $this->url,
                    'target' => $this->target,
                    'postData' => $this->postData,
                ]
            ]
        ];

        if ($this->target == 'dialog') {
            $vueData['operation'][$this->name]['dialog'] = $this->dialog;
        } elseif ($this->target == 'drawer') {
            $vueData['operation'][$this->name]['drawer'] = $this->drawer;
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
            'operationClick' => 'function (name, row) {
                var option = this.operation[name];
                this.operationAction(name, option, row);
            }'
        ];
    }

}
