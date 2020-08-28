<?php

namespace Be\Plugin\Detail\Item;

/**
 * 明细驱动
 */
abstract class DetailItem
{

    public $name = null; // 键名
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
        } else {
            $this->name = 'n'.(self::$nameIndex++);
        }

        if (isset($params['label'])) {
            $this->label = $params['label'];
        }

        if (isset($params['value'])) {
            $this->value = $params['value'];
        }

        if (isset($params['ui'])) {
            $this->ui = $params['ui'];
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
