<?php

namespace Be\Plugin\Lists;


/**
 * 按钮
 */
abstract class Item
{

    protected $name = ''; // 键名
    protected $label = ''; // 配置项中文名称
    protected $value = ''; // 值
    protected $newValue = ''; // 新值
    protected $description = ''; // 描述
    protected $keyValues = null; // 可选值键值对

    protected $ui = []; // UI界面参数

    /**
     * 构造函数
     *
     * @param array $params 参数
     */
    public function __construct($params = [])
    {

        if (isset($params['name'])) {
            $name = $params['name'];

            if (is_callable($name)) {
                $this->name = $name();
            } else {
                $this->name = $name;
            }
        }

        if (isset($params['label'])) {
            $label = $params['label'];

            if (is_callable($label)) {
                $this->label = $label();
            } else {
                $this->label = $label;
            }
        }

        if (isset($params['value'])) {
            $value = $params['value'];

            if (is_callable($value)) {
                $this->value = $value();
            } else {
                $this->value = $value;
            }
        }

        if (isset($params['description'])) {
            $description = $params['description'];

            if (is_callable($description)) {
                $this->description = $description();
            } else {
                $this->description = $description;
            }
        }

        if (isset($params['ui'])) {
            $ui = $params['ui'];
            if (is_callable($ui)) {
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
     * 编辑
     *
     * @return string | array
     */
    public function getHtml()
    {
        return '';
    }


    public function __get($property)
    {
        if (isset($this->$property)) {
            return ($this->$property);
        } else {
            return null;
        }
    }

}
