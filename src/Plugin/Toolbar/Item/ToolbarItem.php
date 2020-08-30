<?php

namespace Be\Plugin\Toolbar\Item;

use Be\Plugin\Curd\Item;

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
        $vueData = [
            'toolbar' => [
                $this->name => [
                    'url' => $this->url,
                    'target' => $this->target,
                    'postData' => $this->postData,
                    'enable' => true,
                ]
            ]
        ];

        if ($this->target == 'dialog') {
            $vueData['toolbar'][$this->name]['dialog'] = $this->dialog;
        } elseif ($this->target == 'drawer') {
            $vueData['toolbar'][$this->name]['drawer'] = $this->drawer;
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
            'toolbarClick' => 'function (name) {
                var option = this.toolbar[name];
                this.toolbarAction(name, option);
            }'
        ];
    }


}
