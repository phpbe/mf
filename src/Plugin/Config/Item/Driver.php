<?php

namespace Be\Plugin\Config\Item;

use Be\System\Annotation\BeConfigItem;
use Be\System\Be;
use Be\System\Exception\PluginException;
use Be\Util\Str;

/**
 * 缓存驱动
 */
trait Driver
{
    protected $config = null; // 注入config

    /**
     * 构造函数
     *
     * @param string $name 键名
     * @param mixed $value 值
     * @param BeConfigItem $annotation 注解参数
     */
    public function __construct($name, $value, $annotation)
    {
        /*
         * 配置项示例:
         * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemInt
         * @be-config-item-label 中文标签
         * @be-config-item-keyValueType hash
         * @be-config-item-keyValues {"1":"男","0":"女","-1":"保密"}
         * @be-config-item-ui []
         */
        $this->name = $name;
        $this->value = $value;

        $this->label = $annotation->value;


        if (isset($params['keyValueType'])) {
            $this->keyValueType = $params['keyValueType'];
        }

        if (isset($params['description'])) {
            $this->description = $params['description'];
        }

        $values = null;
        switch ($this->keyValueType) {
            case 'sql':
                if (isset($params['keyValues'])) {
                    $keyValues = json_decode($params['keyValues'], true);
                    if (isset($keyValues['sql'])) {
                        $sql = $keyValues['sql'];

                        $cache = 0;
                        if (isset($keyValues['cache'])) {
                            $cache = intval($keyValues['cache']);
                        }

                        if ($cache > 0) {
                            $keyValues = Be::getDb()->withCache($cache)->getKeyValues($sql);
                        } else {
                            $keyValues = Be::getDb()->getKeyValues($sql);
                        }

                        $this->keyValues = $keyValues;
                    }
                } elseif (isset($params['values'])) {
                    $tmpValues = json_decode($params['values'], true);
                    if (isset($tmpValues['sql'])) {
                        $sql = $tmpValues['sql'];

                        $cache = 0;
                        if (isset($tmpValues['cache'])) {
                            $cache = intval($tmpValues['cache']);
                        }

                        if ($cache > 0) {
                            $values = Be::getDb()->withCache($cache)->getValues($sql);
                        } else {
                            $values = Be::getDb()->getValues($sql);
                        }
                    }
                }
                break;
            case 'code':
                if (isset($params['keyValues'])) {
                    $keyValues = trim($params['keyValues']);
                    if ($keyValues) {

                        $newKeyValues = null;
                        try {
                            if (strpos($keyValues, 'return ') === false) {
                                $keyValues = 'return ' . $keyValues;
                            }

                            if (substr($keyValues, 0, -1) != ';') {
                                $keyValues .= ';';
                            }

                            $newKeyValues = eval($keyValues);
                        } catch (\Throwable $e) {

                        }

                        if (is_array($newKeyValues)) {
                            $this->keyValues = $newKeyValues;
                        }
                    }
                } elseif (isset($params['values'])) {
                    $tmpValues = trim($params['values']);
                    if ($tmpValues) {

                        $newValues = null;
                        try {
                            if (strpos($tmpValues, 'return ') === false) {
                                $tmpValues = 'return ' . $tmpValues;
                            }

                            if (substr($tmpValues, 0, -1) != ';') {
                                $tmpValues .= ';';
                            }

                            $newValues = eval($tmpValues);
                        } catch (\Throwable $e) {

                        }

                        if (is_array($newValues)) {
                            $values = $newValues;
                        }
                    }
                }
                break;
            default:
                if (isset($params['keyValues'])) {
                    if (is_array($params['keyValues'])) {
                        $this->keyValues = $params['keyValues'];
                    } else {
                        $keyValues = trim($params['keyValues']);
                        if ($keyValues) {
                            $keyValues = json_decode($params['keyValues'], true);
                            $this->keyValues = $keyValues;
                        }
                    }
                } elseif (isset($params['values'])) {
                    if (is_array($params['values'])) {
                        $values = $params['values'];
                    } else {
                        $tmpValues = trim($params['values']);
                        if ($tmpValues) {
                            $values = json_decode($tmpValues, true);
                        }
                    }
                }
        }

        if ($this->keyValues === null && $values !== null && is_array($values) && count($values) > 0) {
            $keyValues = [];
            foreach ($values as $value) {
                $keyValues[$value] = $value;
            }
            $this->keyValues = $keyValues;
        }

        if (isset($params['option'])) {
            $this->option = json_decode($params['option'], true);
        }

        if (isset($params['ui'])) {
            $this->ui = json_decode($params['ui'], true);
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
     * 注入配置项所属的配置类
     *
     * @param $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

}
