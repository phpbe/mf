<?php
namespace Be\App\System\Service;

use App\System\Helper\DocComment;
use Be\System\Be;
use Be\System\Service;
use Be\System\Exception\ServiceException;

class Config extends Service
{

    /**
     * 获取配置文件树状结构
     */
    public function getConfigTree()
    {
        $appNames = Be::getService('System.App')->getAppNames();

        $configTree = [];
        foreach ($appNames as $appName) {

            $dir = Be::getRuntime()->getRootPath() . Be::getProperty('App.'.$appName)->path . '/Config';
            $configs = array();
            if (file_exists($dir) && is_dir($dir)) {
                $fileNames = scandir($dir);
                foreach ($fileNames as $fileName) {
                    if ($fileName != '.' && $fileName != '..' && is_file($dir . '/' . $fileName)) {
                        $configName = substr($fileName, 0, -4);
                        $configSummary = $this->getConfigSummary($appName, $configName);
                        if (isset($configSummary['name'])) {
                            $configs[] = $configSummary;
                        }
                    }
                }
            }

            if (count($configs)) {
                $configTree[] = [
                    'app' => $appName,
                    'configs' => $configs
                ];
            }
        }

        return $configTree;
    }


    /**
     * 获取配置文件文档注释
     *
     * @param string $app 应用名
     * @param string $config 配置文件名
     * @return array
     */
    public function getConfigSummary($app, $config)
    {
        $className = 'Be\\App\\' . $app . '\\Config\\' . $config;

        if (!class_exists($className)) return [];
        $reflection = $this->getReflectionClass($className);

        $config = [
            'app' => $app,
            'name' => $config,
        ];

        // 类注释
        $classComment = $reflection->getDocComment();
        $parseClassComments = DocComment::parse($classComment);
        foreach ($parseClassComments as $key => $val) {
            if (substr($key, 0, 10) == 'be-config-') {
                $config[substr($key,10)] = $val;
            }
        }

        return $config;
    }

    private $reflectionClass = [];
    private function getReflectionClass($className) {
        if (isset($this->reflectionClass[$className])) return $this->reflectionClass[$className];

        if (class_exists($className)) {
            $this->reflectionClass[$className] = new \ReflectionClass($className);
            return $this->reflectionClass[$className];
        }

        return null;
    }


    /**
     * 获取配置文件文档注释
     *
     * @param string $app 应用名
     * @param string $config 配置文件名
     * @return array
     */
    public function getConfig($app, $config)
    {
        $className = 'Be\\App\\' . $app . '\\Config\\' . $config;
        if (!class_exists($className)) return [];

        $instance = Be::getConfig($app.'.'.$config);


        $config = [
            'app' => $app,
            'name' => $config
        ];

        $reflection = $this->getReflectionClass($className);

        // 类注释
        $classComment = $reflection->getDocComment();
        $parseClassComments = DocComment::parse($classComment);
        foreach ($parseClassComments as $key => $val) {
            if (substr($key, 0, 10) == 'be-config-') {
                $config[substr($key,10)] = $val;
            }
        }

        if (isset($config['test'])) {
            try {
                $config['test'] = eval('return ' . $config['test'] . ';');
            } catch (\Throwable $e) {

            }
        }

        $configItems = [];
        $properties = $reflection->getProperties(\ReflectionMethod::IS_PUBLIC);

        foreach ($properties as &$property) {
            $propertyName = $property->getName();
            $propertyComment = $property->getDocComment();
            $propertyComments = DocComment::parse($propertyComment);


            $keyValueType = null;
            $dataKeyValues = null;
            $dataValues = null;

            $params = [];
            foreach ($propertyComments as $key => $val) {
                if (substr($key, 0, 15) == 'be-config-item-') {

                    $k = substr($key,15);
                    switch ($k) {
                        case 'option':
                        case 'ui':
                            $params[$k] = json_decode($val, true);
                            break;

                        case 'keyValueType':
                            $keyValueType = $val;
                            break;

                        case 'keyValues':
                            $dataKeyValues = $val;
                            break;

                        case 'values':
                            $dataValues = $val;
                            break;

                        default:
                            $params[$k] = $val;

                    }
                }
            }

            $keyValues = null;
            $values = null;
            switch ($keyValueType) {
                case 'sql':
                    if ($dataKeyValues !== null) {
                        $dataKeyValues = json_decode($dataKeyValues, true);
                        if (isset($dataKeyValues['sql'])) {
                            $sql = $dataKeyValues['sql'];

                            $cache = 0;
                            if (isset($dataKeyValues['cache'])) {
                                $cache = intval($dataKeyValues['cache']);
                            }

                            if ($cache > 0) {
                                $keyValues = Be::getDb()->withCache($cache)->getKeyValues($sql);
                            } else {
                                $keyValues = Be::getDb()->getKeyValues($sql);
                            }
                        }
                    } elseif ($dataValues !== null) {
                        $tmpValues = json_decode($dataValues, true);
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
                    if ($dataKeyValues !== null) {
                        $dataKeyValues = trim($dataKeyValues);
                        if ($dataKeyValues) {

                            $newKeyValues = null;
                            try {
                                if (strpos($dataKeyValues, 'return ') === false) {
                                    $dataKeyValues = 'return ' . $dataKeyValues;
                                }

                                if (substr($dataKeyValues, 0, -1) != ';') {
                                    $dataKeyValues .= ';';
                                }

                                $newKeyValues = eval($dataKeyValues);
                            } catch (\Throwable $e) {

                            }

                            if (is_array($newKeyValues)) {
                                $keyValues = $newKeyValues;
                            }
                        }
                    } elseif ($dataValues !== null) {
                        $tmpValues = trim($dataValues);
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
                    if ($dataKeyValues !== null) {
                        if (is_array($dataKeyValues)) {
                            $keyValues = $dataKeyValues;
                        } else {
                            $dataKeyValues = trim($dataKeyValues);
                            if ($dataKeyValues) {
                                $dataKeyValues = json_decode($dataKeyValues, true);
                                if (is_array($dataKeyValues)) {
                                    $keyValues = $dataKeyValues;
                                }
                            }
                        }
                    } elseif ($dataValues !== null) {
                        if (is_array($dataValues)) {
                            $values = $dataValues;
                        } else {
                            $dataValues = trim($dataValues);
                            if ($dataValues) {
                                $values = json_decode($dataValues, true);
                            }
                        }
                    }
            }

            if ($keyValues === null) {
                if ($values !== null && is_array($values) && count($values) > 0) {
                    $keyValues = [];
                    foreach ($values as $value) {
                        $keyValues[$value] = $value;
                    }
                    $params['keyValues'] = $keyValues;
                }
            } else {
                $params['keyValues'] = $keyValues;
            }


            if (isset($params['driver'])) {
                $driverClass = $params['driver'];
                $configItemDriver = new $driverClass($propertyName, $instance->$propertyName, $params);
                $configItemDriver->setConfig($config);
                $configItems[$propertyName] = $configItemDriver;
            }
        }

        $config['items'] = $configItems;
        return $config;
    }



    /*
     * 保存配置文件到指定咱径
     *
     * @param string $appName 应用名称
     * @param string $configName 配置名称
     * @return bool 是否保存成功
     */
    public function saveConfig($appName, $configName, $data)
    {
        $class = '\\App\\' . $appName . '\\Config\\' . $configName;
        $instance = new $class();
        $vars = get_object_vars($instance);

        $config = $this->getConfig($appName, $configName);

        $code = "<?php\n";
        $code .= 'namespace Be\\Data\\System\\Config\\' . $appName . ';' . "\n\n";
        $code .= 'class ' . $configName . "\n";
        $code .= "{\n";

        foreach ($vars as $k => $v) {

            $found = false;
            foreach ($config['items'] as $configItem) {
                $itemName = $configItem->name;
                if ($k == $itemName) {

                    if (!isset($data[$itemName])) {
                        throw new ServiceException('参数 ' . $configItem->label . ' (' . $itemName . ') 缺失！');
                    }

                    $configItem->submit($data);
                    $code .= '  public $' . $itemName . ' = ' . var_export($configItem->newValue, true) . ';' . "\n";
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $code .= '  public $' . $k . ' = ' . var_export($v, true) . ';' . "\n";
            }
        }

        $code .= "}\n";

        $path = Be::getRuntime()->getDataPath() . '/System/Config/' . $appName. '/' . $configName . '.php';
        return file_put_contents($path, $code, LOCK_EX);
    }

    /*
     * 恢复默认值
     *
     * @param string $appName 应用名称
     * @param string $configName 配置名称
     * @return bool 是否恢复默认值成功
     */
    public function resetConfig($appName, $configName)
    {
        $path = Be::getRuntime()->getDataPath() . '/System/Config/' . $appName. '/' . $configName . '.php';
        if(file_exists($path)) @unlink($path);
        return true;
    }
}






