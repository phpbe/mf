<?php

namespace Be\Plugin\Curd\ToolbarItem;

use Be\Plugin\Curd\Item;

/**
 * 工具栏驱动
 */
abstract class ToolbarItem extends Item
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
            'toolbar' => [
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
            'toolbarClick' => 'function (e, name) {
                var oToolbar = this.toolbar[name];
                this.toolbarAction(oToolbar);
            }'
        ];
    }

}
