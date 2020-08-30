<?php

namespace Be\Plugin\Detail\Item;

/**
 * 明细驱动
 */
abstract class DetailItem
{

    public $name = ''; // 键名
    public $label = ''; // 配置项中文名称
    public $value = ''; // 值
    public $ui = []; // UI界面参数

    protected static $nameIndex = 0;

    /**
     * 构造函数
     *
     * @param array $params 参数
     */
    public function __construct($params = [])
    {
        if (isset($params['name'])) {
            $this->name = $params['name'];
        }

        if (isset($params['label'])) {
            $label = $params['label'];
            if ($label instanceof \Closure) {
                $this->label = $label();
            } else {
                $this->label = $label;
            }
        }

        if (isset($params['value'])) {
            $value = $params['value'];
            if ($value instanceof \Closure) {
                $this->value = $value();
            } else {
                $this->value = $value;
            }
        }

        if (isset($params['ui'])) {
            $ui = $params['ui'];
            if ($ui instanceof \Closure) {
                $this->ui = $ui();
            } else {
                $this->ui = $ui;
            }
        }

        if (!isset($this->ui['form-item']['label'])) {
            $this->ui['form-item']['label'] = htmlspecialchars($this->label);
        }
    }

    /**
     * 获取HTML内容
     *
     * @return string
     */
    public function getHtml()
    {
        return '';
    }

    /**
     * 获取 vue data
     *
     * @return false | array
     */
    public function getVueData()
    {
        return false;
    }

    /**
     * 获取 vue 方法
     *
     * @return false | array
     */
    public function getVueMethods()
    {
        return false;
    }

}
