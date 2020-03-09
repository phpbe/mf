<?php

namespace Be\System\App\DataItem;

use Be\System\Be;
use Be\System\Service\ServiceException;
use Be\Util\Str;

/**
 * 缓存驱动
 */
abstract class Driver
{
    protected $name = ''; // 键名
    protected $value = ''; // 值
    protected $label = ''; // 配置项中文名称
    protected $description = ''; // 描述
    protected $newValue = ''; // 新值

    protected $keyValues = null; // 可选值键值对

    protected $option = []; // 应用参数
    protected $ui = []; // UI界面参数

    /**
     * 构造函数
     *
     * @param string $name 键名
     * @param mixed $value 值
     * @param array $params 注解参数
     */
    public function __construct($name, $value, $params = array())
    {

        $this->name = $name;
        $this->value = $value;

        if (isset($params['label'])) {
            $this->label = $params['label'];
        }

        if (isset($params['description'])) {
            $this->description = $params['description'];
        }

        if (isset($params['keyValues'])) {
            $this->keyValues = $params['keyValues'];
        }

        if (isset($params['values'])) {
            $values = $params['values'];
            if ($this->keyValues === null && $values !== null && is_array($values) && count($values) > 0) {
                $keyValues = [];
                foreach ($values as $value) {
                    $keyValues[$value] = $value;
                }
                $this->keyValues = $keyValues;
            }
        }

        if (isset($params['option'])) {
            $this->option = $params['option'];
        }

        if (isset($params['fn'])) {
            $this->fn = $params['fn'];
        }

        if (isset($params['ui'])) {
            $this->ui = $params['ui'];
        }

        if (!isset($this->ui['form-item']['label'])) {
            $this->ui['form-item']['label'] = htmlspecialchars($this->label);
        }

        if (!isset($this->ui['form-item'][':label-col'])) {
            $this->ui['form-item'][':label-col'] = '{span:6}';
        }

        if (!isset($this->ui['form-item'][':wrapper-col'])) {
            $this->ui['form-item'][':wrapper-col'] = '{span:18}';
        }

        if ($this->description) {
            if (!isset($this->ui['form-item']['help'])) {
                $this->ui['form-item']['help'] = htmlspecialchars($this->description);
            }
        }
    }

    /**
     * 编辑
     *
     * @return string | array
     */
    public function getEditHtml()
    {
        return '';
    }

    /**
     * 编辑
     *
     * @return false | array
     */
    public function getEditData()
    {
        return false;
    }

    /**
     * 编辑
     *
     * @return false | array
     */
    public function getEditMethods()
    {
        return false;
    }

    /**
     * 明细
     *
     * @return string
     */
    public function getDetailHtml()
    {
        if ($this->keyValues !== null && is_array($this->keyValues) && isset($this->keyValues[$this->value])) {
            return $this->keyValues[$this->value];
        }

        return $this->value;
    }

    /**
     * 提交处理
     *
     * @param $data
     * @throws \Exception
     */
    public function submit($data)
    {
        if (isset($data[$this->name])) {
            $newValue =  $data[$this->name];
            if (!is_array($newValue) && !is_object($newValue)) {
                $newValue =  htmlspecialchars_decode($newValue);
            }
            $this->newValue = $newValue;
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


    public function __get($property)
    {
        if (isset($this->$property)) {
            return ($this->$property);
        } else {
            return null;
        }

    }
}
