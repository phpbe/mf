<?php

namespace Be\Plugin\Lists\Search;

use Be\System\Be;
use Be\System\Exception\ServiceException;
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

        if (is_callable($value)) {
            $this->value = $value();
        } else {
            $this->value = $value;
        }

        if (isset($params['label'])) {
            $label = $params['label'];

            if (is_callable($label)) {
                $this->label = $label();
            } else {
                $this->label = $label;
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

        if (isset($params['keyValues'])) {
            $keyValues = $params['keyValues'];

            if (is_callable($keyValues)) {
                $this->keyValues = $keyValues();
            } else {
                $this->keyValues = $keyValues;
            }
        }

        if (isset($params['values']) && $this->keyValues === null) {
            $values = $params['values'];

            if (is_callable($values)) {
                $values = $values();
            }

            if ($values !== null && is_array($values) && count($values) > 0) {
                $keyValues = [];
                foreach ($values as $value) {
                    $keyValues[$value] = $value;
                }
                $this->keyValues = $keyValues;
            }
        }

        if (isset($params['option'])) {
            $option = $params['option'];
            if (is_callable($option)) {
                $this->option = $option();
            } else {
                $this->option = $option;
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
    public function getHtml()
    {
        return '';
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
            $newValue = $data[$this->name];
            if (!is_array($newValue) && !is_object($newValue)) {
                $newValue =  htmlspecialchars_decode($newValue);
            }
            $this->newValue = $newValue;
        }
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
     * 查询SQL
     *
     * @return string
     */
    public function buildSql()
    {
        $where = [];
        if ($this->newValue) {

            if (isset($this->option['db'])) {
                $db = Be::getDb($this->option['db']);
            } else {
                $db = Be::getDb();
            }

            if (isset($this->option['table'])) {
                $where = $db->quoteKey($this->option['table']) . '.';
            }

            $field = isset($this->option['field']) ? $this->option['field'] : $this->name;

            $where[] =  $db->quoteKey($field) . '=' . $db->quoteValue($this->newValue);
        }

        return $where;
    }

}
