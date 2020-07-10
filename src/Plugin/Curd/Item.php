<?php

namespace Be\Plugin\Curd;

use Be\System\Request;

/**
 * 按钮
 */
abstract class Item
{

    public $name = ''; // 键名
    public $label = ''; // 配置项中文名称
    public $value = ''; // 值

    public $keyValues = null; // 可选值键值对

    public $url = ''; // 网址

    public $ui = []; // UI界面参数

    public $postData = []; // 有后端请求时的附加上的数据
    public $target = 'drawer';
    public $dialog = [];
    public $drawer = [];

    protected static $nameIndex = 0;

    /**
     * 构造函数
     *
     * @param array $params 参数
     * @param object $tuple 行数据
     */
    public function __construct($params = [], $tuple = null)
    {
        if (isset($params['name'])) {
            $name = $params['name'];
            if (is_callable($name)) {
                if ($tuple !== null) {
                    $this->name = $name($tuple);
                } else {
                    $this->name = $name();
                }
            } else {
                $this->name = $name;
            }
        } else {
            $this->name = 'n'.(self::$nameIndex++);
        }

        if (isset($params['label'])) {
            $label = $params['label'];
            if (is_callable($label)) {
                if ($tuple !== null) {
                    $this->label = $label($tuple);
                } else {
                    $this->label = $label();
                }
            } else {
                $this->label = $label;
            }
        }

        if (isset($params['value'])) {
            $value = $params['value'];
            if (is_callable($value)) {
                if ($tuple !== null) {
                    $this->value = $value($tuple);
                } else {
                    $this->value = $value();
                }
            } else {
                $this->value = $value;
            }
        }

        if (isset($params['keyValues'])) {
            $keyValues = $params['keyValues'];
            if (is_callable($keyValues)) {
                if ($tuple !== null) {
                    $this->keyValues = $keyValues($tuple);
                } else {
                    $this->keyValues = $keyValues();
                }
            } else {
                $this->keyValues = $keyValues;
            }
        } else {
            if (isset($params['values'])) {
                $values = $params['values'];
                if (is_callable($values)) {
                    if ($tuple !== null) {
                        $values = $values($tuple);
                    } else {
                        $values = $values();
                    }
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
                if ($tuple !== null) {
                    $this->url = $url($tuple);
                } else {
                    $this->url = $url();
                }
            } else {
                $this->url = $url;
            }
        } else {
            if (isset($params['task'])) {
                $task = $params['task'];
                if (is_callable($task)) {
                    if ($tuple !== null) {
                        $task = $task($tuple);
                    } else {
                        $task = $task();
                    }
                }

                $url = Request::url();
                $url .= (strpos($url, '?') === false ? '?' : '&') . 'task=' . $task;
                $this->url = $url;
            }
        }

        if (isset($params['ui'])) {
            $ui = $params['ui'];
            if (is_callable($ui)) {
                if ($tuple !== null) {
                    $this->ui = $ui($tuple);
                } else {
                    $this->ui = $ui();
                }
            } else {
                $this->ui = $ui;
            }
        }

        if (isset($params['postData'])) {
            $postData = $params['postData'];
            if (is_callable($postData)) {
                if ($tuple !== null) {
                    $this->postData = $postData($tuple);
                } else {
                    $this->postData = $postData();
                }
            } else {
                $this->postData = $postData;
            }
        }


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
     * 获取HTML内容
     *
     * @return string | array
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
