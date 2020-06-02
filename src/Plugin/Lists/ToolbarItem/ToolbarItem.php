<?php

namespace Be\Plugin\Lists\ToolbarItem;

use Be\Plugin\Lists\Item;

/**
 * 工具栏驱动
 */
abstract class ToolbarItem extends Item
{

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
                    'data' => $this->data,
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
