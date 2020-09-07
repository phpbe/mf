<?php

namespace Be\Plugin\Form\Item;

use Be\System\Be;
use Be\System\Exception\PluginException;

/**
 * 表单项
 */
abstract class FormItem
{

    protected $name = ''; // 键名
    protected $label = ''; // 配置项中文名称
    protected $value = ''; // 值
    protected $valueType = 'string'; // 值类型
    protected $keyValues = null; // 可选值键值对
    protected $description = ''; // 描述
    protected $ui = []; // UI界面参数
    protected $newValue = ''; // 新值
    protected $required = false; // 是否必填
    protected $disabled = false; // 是否不可编辑

    /**
     * 构造函数
     *
     * @param array $params 参数
     * @throws PluginException
     */
    public function __construct($params = [])
    {
        if (!isset($params['name'])) {
            throw new PluginException('表单项参数 name 缺失');
        }

        $name = $params['name'];
        if ($name  instanceof \Closure) {
            $this->name = $name();
        } else {
            $this->name = $name;
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

        if (isset($params['ui'])) {
            $ui = $params['ui'];
            if ($ui instanceof \Closure) {
                $this->ui = $ui();
            } else {
                $this->ui = $ui;
            }
        }

        if (isset($params['description'])) {
            $description = $params['description'];
            if ($description instanceof \Closure) {
                $this->description = $description();
            } else {
                $this->description = $description;
            }
        }

        if (isset($params['required'])) {
            $required = $params['required'];
            if ($required instanceof \Closure) {
                $this->required = $required();
            } else {
                $this->required = $required;
            }
        }

        if (isset($params['disabled'])) {
            $disabled = $params['disabled'];
            if ($disabled instanceof \Closure) {
                $this->disabled = $disabled();
            } else {
                $this->disabled = $disabled;
            }
        }

        if (!isset($this->ui['form-item']['prop'])) {
            $this->ui['form-item']['prop'] = $name;
        }

        if (!isset($this->ui['form-item']['label'])) {
            $this->ui['form-item']['label'] = htmlspecialchars($this->label);
        }

    }

    /**
     * 获取需要引入的 JS 文件
     *
     * @return false | array
     */
    public function getJs()
    {
        return false;
    }


    /**
     * 获取需要引入的 CSS 文件
     *
     * @return false | array
     */
    public function getCss()
    {
        return false;
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

    /**
     * 获取 vue 钩子
     *
     * @return false | array
     */
    public function getVueHooks()
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
     * @throws PluginException
     */
    public function submit($data)
    {
        if (isset($data[$this->name]) && $data[$this->name] != '') {
            $newValue = $data[$this->name];
            switch ($this->valueType) {
                case 'array(int)':
                    $newValue =  htmlspecialchars_decode($newValue);
                    $newValue = json_decode($newValue, true);
                    if (NULL === $newValue) {
                        throw new PluginException('参数 ' . $this->label . ' (' . $this->name . ') 数据格式非有效的 JSON！');
                    }
                    $newValue = array_map('intval',$newValue);
                    $this->newValue = $newValue;
                    break;
                case 'array(float)':
                    $newValue =  htmlspecialchars_decode($newValue);
                    $newValue = json_decode($newValue, true);
                    if (NULL === $newValue) {
                        throw new PluginException('参数 ' . $this->label . ' (' . $this->name . ') 数据格式非有效的 JSON！');
                    }
                    $newValue = array_map('floatval',$newValue);
                    $this->newValue = $newValue;
                    break;
                case 'array':
                case 'array(string)':
                    $newValue = htmlspecialchars_decode($newValue);
                    $newValue = json_decode($newValue, true);
                    if (NULL === $newValue) {
                        throw new PluginException('参数 ' . $this->label . ' (' . $this->name . ') 数据格式非有效的 JSON！');
                    }
                    $newValue = array_map('trim',$newValue);
                    $this->newValue = $newValue;
                    break;
                case 'mixed':
                    $newValue = htmlspecialchars_decode($newValue);
                    $newValue = json_decode($newValue, true);
                    if (NULL === $newValue) {
                        throw new PluginException('参数 ' . $this->label . ' (' . $this->name . ') 数据格式非有效的 JSON！');
                    }
                    $this->newValue = $newValue;
                    break;
                case 'bool':
                    $this->newValue = $data[$this->name] ? true : false;
                    break;
                case 'int':
                    if (!is_numeric($newValue)) {
                        throw new PluginException('参数 ' . $this->label . ' (' . $this->name . ') 不是合法的数字');
                    }
                    $this->newValue = intval($newValue);
                    break;
                case 'float':
                    if (!is_numeric($newValue)) {
                        throw new PluginException('参数 ' . $this->label . ' (' . $this->name . ') 不是合法的数字');
                    }
                    $this->newValue = floatval($newValue);
                    break;
                case 'string':
                    $newValue = htmlspecialchars_decode($newValue);
                    $this->newValue = trim($newValue);
                    break;
                default:
                    if (!is_array($newValue) && !is_object($newValue)) {
                        $newValue = htmlspecialchars_decode($newValue);
                    }
                    $this->newValue = $newValue;
            }
        }
    }


}
