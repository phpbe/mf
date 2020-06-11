<?php

namespace Be\Plugin\Editor\EditorItem;

use Be\System\Be;

/**
 * 字段驱动
 */
abstract class EditorItem
{

    protected $name = ''; // 键名
    protected $label = ''; // 配置项中文名称
    protected $value = ''; // 值
    protected $newValue = null; // 新值

    protected $keyValues = null; // 可选值键值对

    protected $url = null; // 网址

    protected $option = null; // 控制项
    protected $data = null; // POST 到后端的数据
    protected $ui = []; // UI界面参数

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

                $runtime = Be::getRuntime();
                $this->url = beUrl($runtime->getAppName() . '.' . $runtime->getControllerName() . '.' . $runtime->getActionName(), ['task' => $task]);
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

        if (!isset($this->ui['form-item']['label'])) {
            $this->ui['form-item']['label'] = htmlspecialchars($this->label);
        }

        if (isset($params['option'])) {
            $option = $params['option'];
            if (is_callable($option)) {
                if ($tuple !== null) {
                    $this->option = $option($tuple);
                } else {
                    $this->option = $option();
                }
            } else {
                $this->option = $option;
            }
        }

        if (isset($params['data'])) {
            $data = $params['data'];
            if (is_callable($data)) {
                if ($tuple !== null) {
                    $this->data = $data($tuple);
                } else {
                    $this->data = $data();
                }
            } else {
                $this->data = $data;
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

    public function __get($property)
    {
        if (isset($this->$property)) {
            return ($this->$property);
        } else {
            return null;
        }
    }

    /**
     * 提交处理
     *
     * @param $data
     * @throws \Exception
     */
    public function submit($data)
    {
        if (isset($data[$this->field])) {
            $newValue = $data[$this->field];
            if (!is_array($newValue) && !is_object($newValue)) {
                $newValue =  htmlspecialchars_decode($newValue);
            }
            $this->newValue = $newValue;
        }
    }

}
