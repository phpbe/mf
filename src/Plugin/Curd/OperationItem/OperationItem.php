<?php

namespace Be\Plugin\Curd\OperationItem;

use Be\Plugin\Curd\Item;

/**
 * 字段驱动
 */
abstract class OperationItem extends Item
{

    public $option = []; // 控制项
    public $postData = []; // 有后端请求时的附加上的数据

    /**
     * 获取 vue data
     *
     * @return false | array
     */
    public function getVueData()
    {
        return [
            'operation' => [
                $this->name => [
                    'option' => $this->option,
                    'postData' => $this->postData,
                ]
            ]
        ];
    }


    /**
     * 获取 vue 方法
     *
     * @return false | array
     */
    public function getVueMethods()
    {
        return [
            'operationClick' => 'function (e) {
                console.log(e);
                //console.log(e.srcElement.dataset.url)
                //var oOperation = this.operation[name];
                //this.operationAction(oOperation);
            }'
        ];
    }

}
