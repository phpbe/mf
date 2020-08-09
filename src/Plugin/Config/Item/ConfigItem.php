<?php

namespace Be\Plugin\Config\Item;

use Be\System\Annotation\BeConfigItem;
use Be\System\Exception\ServiceException;

/**
 * 按钮
 */
abstract class ConfigItem
{
    public $appName = ''; // 应用名
    public $configName = ''; // 配置名
    public $name = ''; // 键名
    public $label = ''; // 配置项中文名称
    public $value = ''; // 值
    public $valueType = 'string'; // 值类型

    public $keyValues = null; // 可选值键值对

    public $description = ''; // 描述
    public $newValue = ''; // 新值

    public $ui = []; // UI界面参数

    /**
     * 构造函数
     *
     * @param string $name 键名
     * @param mixed $value 值
     * @param BeConfigItem $annotation 注解参数
     */
    public function __construct($name, $value, $annotation)
    {
        $this->name = $name;
        $this->value = $value;
        $this->label = $annotation->value;

        if (isset($annotation->valueType)) {
            $this->valueType = $annotation->valueType;
        }

        if (isset($annotation->keyValues)) {
            $this->keyValues = $annotation->keyValues;
        } else {
            if (isset($annotation->values)) {
                $keyValues = [];
                foreach ($annotation->values as $value) {
                    $keyValues[$value] = $value;
                }
                $this->keyValues = $keyValues;
            }
        }

        if (isset($annotation->description)) {
            $this->description = $annotation->description;
        }

        if (isset($annotation->ui)) {
            $this->ui = $annotation->ui;
        }

        if (!isset($this->ui['form-item']['label'])) {
            $this->ui['form-item']['label'] = htmlspecialchars($this->label);
        }

        if ($this->description) {
            if (!isset($this->ui['form-item']['help'])) {
                $this->ui['form-item']['help'] = htmlspecialchars($this->description);
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

    /**
     * 提交处理
     *
     * @param $data
     * @throws ServiceException
     */
    public function submit($data)
    {
        if (isset($data[$this->name])) {
            $newValue = $data[$this->name];
            switch ($this->valueType) {
                case 'array(int)':
                    $newValue =  htmlspecialchars_decode($newValue);
                    $newValue = json_decode($newValue, true);
                    if (NULL === $newValue) {
                        throw new ServiceException('参数 ' . $this->label . ' (' . $this->name . ') 数据格式非有效的 JSON！');
                    }
                    $newValue = array_walk($newValue, 'intval');
                    $this->newValue = $newValue;
                    break;
                case 'array(float)':
                    $newValue =  htmlspecialchars_decode($newValue);
                    $newValue = json_decode($newValue, true);
                    if (NULL === $newValue) {
                        throw new ServiceException('参数 ' . $this->label . ' (' . $this->name . ') 数据格式非有效的 JSON！');
                    }
                    $newValue = array_walk($newValue, 'floatval');
                    $this->newValue = $newValue;
                    break;
                case 'array':
                case 'array(string)':
                    $newValue = htmlspecialchars_decode($newValue);
                    $newValue = json_decode($newValue, true);
                    if (NULL === $newValue) {
                        throw new ServiceException('参数 ' . $this->label . ' (' . $this->name . ') 数据格式非有效的 JSON！');
                    }
                    $newValue = array_walk($newValue, 'trim');
                    $this->newValue = $newValue;
                    break;
                case 'mixed':
                    $newValue = htmlspecialchars_decode($newValue);
                    $newValue = json_decode($newValue, true);
                    if (NULL === $newValue) {
                        throw new ServiceException('参数 ' . $this->label . ' (' . $this->name . ') 数据格式非有效的 JSON！');
                    }
                    $this->newValue = $newValue;
                    break;
                case 'bool':
                    $this->newValue = $data[$this->name] ? true : false;
                    break;
                case 'int':
                    if (!is_numeric($newValue)) {
                        throw new ServiceException('参数 ' . $this->label . ' (' . $this->name . ') 不是合法的数字');
                    }
                    $this->newValue = intval($newValue);
                    break;
                case 'float':
                    if (!is_numeric($newValue)) {
                        throw new ServiceException('参数 ' . $this->label . ' (' . $this->name . ') 不是合法的数字');
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

    /**
     * 获取值的字符形式
     *
     * @return string
     */
    public function getValueString()
    {
        if (is_array($this->value) || is_object($this->value)) {
            return json_encode($this->value);
        }
        return $this->value;
    }

    /**
     * 获取新值的字符形式
     *
     * @return string
     */
    public function getNewValueString()
    {
        if (is_array($this->newValue) || is_object($this->newValue)) {
            return json_encode($this->newValue);
        }
        return $this->newValue;
    }

}
