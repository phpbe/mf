<?php

namespace Be\Plugin\Curd\OperationItem;

use Be\Plugin\Curd\Item;

/**
 * 字段驱动
 */
abstract class OperationItem extends Item
{

    public $postData = []; // 有后端请求时的附加上的数据

    public $target = 'drawer';
    public $dialog = [];
    public $drawer = [];

    /**
     * 构造函数
     *
     * @param array $params 参数
     * @param object $tuple 行数据
     */
    public function __construct($params = [], $tuple = null)
    {
        parent::__construct($params, $tuple);

        if (isset($params['target'])) {
            $target = $params['target'];
            if (is_callable($target)) {
                if ($tuple !== null) {
                    $this->target = $target($tuple);
                } else {
                    $this->target = $target();
                }
            } else {
                $this->target = $target;
            }
        }

        if ($this->target == 'dialog') {
            if (isset($params['dialog'])) {
                $dialog = $params['dialog'];
                if (is_callable($dialog)) {
                    if ($tuple !== null) {
                        $this->dialog = $dialog($tuple);
                    } else {
                        $this->dialog = $dialog();
                    }
                } else {
                    $this->dialog = $dialog;
                }
            }

            if (!isset($this->dialog['title'])) {
                $this->dialog['title'] = $this->label;
            }
        } elseif ($this->target == 'drawer') {
            if (isset($params['drawer'])) {
                $drawer = $params['drawer'];
                if (is_callable($drawer)) {
                    if ($tuple !== null) {
                        $this->drawer = $drawer($tuple);
                    } else {
                        $this->drawer = $drawer();
                    }
                } else {
                    $this->drawer = $drawer;
                }
            }

            if (!isset($this->drawer['title'])) {
                $this->drawer['title'] = $this->label;
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

}
