<?php

namespace Be\Plugin\Lists;

use Be\System\Be;


/**
 * 按钮
 */
abstract class Item
{

    protected $name = ''; // 键名
    protected $label = ''; // 配置项中文名称
    protected $value = ''; // 值
    protected $newValue = ''; // 新值

    protected $keyValues = null; // 可选值键值对
    protected $url = null; // 描述

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

        if (isset($params['keyValues'])) {
            $keyValues = $params['keyValues'];
            if (is_callable($keyValues)) {
                $this->keyValues = $keyValues();
            } else {
                $this->keyValues = $keyValues;
            }
        } else {
            if (isset($params['values'])) {
                $values = $params['values'];
                if (is_callable($values)) {
                    $values = $values();
                }

                $keyValues = [];
                foreach ($values as $value) {
                    $keyValues[$value] = $value;
                }
                $this->keyValues = $keyValues;
            }
        }

        if (isset($params['url'])) {
            $url = $params['url'];
            if (is_callable($url)) {
                $this->url = $url();
            } else {
                $this->url = $url;
            }
        } else {
            if (isset($params['action'])) {
                $action = $params['action'];
                if (is_callable($action)) {
                    $action = $action();
                }

                if (strpos($action, '.') === false) {
                    $runtime = Be::getRuntime();
                    $this->url = url($runtime->getAppName() . '.' . $runtime->getControllerName() . '.' . $action);
                } else {
                    $this->url = url($action);
                }
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
