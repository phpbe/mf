<?php

namespace Be\Plugin\Curd;

use Be\System\Be;
use Be\System\Request;

/**
 * 按钮
 */
abstract class Item
{

    public $name = null; // 键名
    public $label = ''; // 配置项中文名称
    public $value = ''; // 值

    public $keyValues = null; // 可选值键值对

    public $url = ''; // 网址

    public $ui = []; // UI界面参数

    public $postData = []; // 有后端请求时的附加上的数据
    public $target = 'drawer';
    public $dialog = ['width' => '600px', 'height' => '400px'];
    public $drawer = ['width' => '40%'];

    protected static $nameIndex = 0;

    /**
     * 构造函数
     *
     * @param array $params 参数
     */
    public function __construct($params = [])
    {
        if (isset($params['name'])) {
            $name = $params['name'];
            if ($name instanceof \Closure) {
                $this->name = $name();
            } else {
                $this->name = $name;
            }
        } else {
            $this->name = 'n' . (self::$nameIndex++);
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

        if (isset($params['keyValues'])) {
            $keyValues = $params['keyValues'];
            if ($keyValues instanceof \Closure) {
                $this->keyValues = $keyValues();
            } else {
                $this->keyValues = $keyValues;
            }
        } else {
            if (isset($params['values'])) {
                $values = $params['values'];
                if ($values instanceof \Closure) {
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
            if ($url instanceof \Closure) {
                $this->url = $url();
            } else {
                $this->url = $url;
            }
        } else {
            if (isset($params['task'])) {
                $task = $params['task'];
                if ($task instanceof \Closure) {
                    $task = $task();
                }

                $url = Request::url();
                $url .= (strpos($url, '?') === false ? '?' : '&') . 'task=' . $task;
                $this->url = $url;
            } elseif (isset($params['action'])) {
                $action = $params['action'];
                if ($action instanceof \Closure) {
                    $action = $action();
                }

                $runtime = Be::getRuntime();
                $appName = $runtime->getAppName();
                $controllerName = $runtime->getControllerName();
                $this->url = beUrl($appName . '.' . $controllerName . '.' . $action);
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

        if (isset($params['postData'])) {
            $postData = $params['postData'];
            if ($postData instanceof \Closure) {
                $this->postData = $postData();
            } else {
                $this->postData = $postData;
            }
        }

        if (isset($params['target'])) {
            $target = $params['target'];
            if ($target instanceof \Closure) {
                $this->target = $target();
            } else {
                $this->target = $target;
            }
        }

        if ($this->target == 'dialog') {
            if (isset($params['dialog'])) {
                $dialog = $params['dialog'];
                if ($dialog instanceof \Closure) {
                    $this->dialog = $dialog();
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
                if ($drawer instanceof \Closure) {
                    $this->drawer = $drawer();
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
