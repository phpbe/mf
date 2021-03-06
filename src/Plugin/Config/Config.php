<?php

namespace Be\Mf\Plugin\Config;

use Be\F\Config\Annotation\BeConfig;
use Be\F\Config\Annotation\BeConfigItem;
use Be\Mf\Be;
use Be\Mf\Plugin\Driver;
use Be\Mf\Plugin\PluginException;

/**
 * 配置
 *
 * Class Config
 * @package Be\Mf\Plugin\Config
 */
class Config extends Driver
{

    public function display()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $appName = isset($this->setting['appName']) ? $this->setting['appName'] : $request->getAppName();

        $configs = [];
        $dir = Be::getRuntime()->getRootPath() . Be::getProperty('App.' . $appName)->getPath() . '/Config';
        if (file_exists($dir) && is_dir($dir)) {
            $fileNames = scandir($dir);
            foreach ($fileNames as $fileName) {
                if ($fileName != '.' && $fileName != '..' && is_file($dir . '/' . $fileName)) {
                    $configName = substr($fileName, 0, -4);
                    $className = '\\Be\\Mf\\App\\' . $appName . '\\Config\\' . $configName;
                    if (class_exists($className)) {
                        $reflection = new \ReflectionClass($className);
                        $classComment = $reflection->getDocComment();
                        $parseClassComments = \Be\F\Util\Annotation::parse($classComment);
                        if (isset($parseClassComments['BeConfig'][0])) {
                            $annotation = new BeConfig($parseClassComments['BeConfig'][0]);
                            $config = $annotation->toArray();
                            if (isset($config['value'])) {
                                $config['label'] = $config['value'];
                                unset($config['value']);
                            }
                            $config['name'] = $configName;
                            $config['url'] = beUrl($request->getRoute(), ['configName' => $configName]);
                            $configs[] = $config;
                        }
                    }
                }
            }
        }
        $response->set('configs', $configs);

        $configName = $request->get('configName', '');
        if (!$configName) {
            $configName = $configs[0]['name'];
        }
        $response->set('configName', $configName);

        $configItemDrivers = [];
        $className = '\\Be\\Mf\\App\\' . $appName . '\\Config\\' . $configName;
        if (class_exists($className)) {
            $configInstance = Be::getConfig($appName . '.' . $configName);
            $reflection = new \ReflectionClass($className);
            $properties = $reflection->getProperties(\ReflectionMethod::IS_PUBLIC);
            foreach ($properties as $property) {
                $itemName = $property->getName();
                $itemComment = $property->getDocComment();
                $parseItemComments = \Be\F\Util\Annotation::parse($itemComment);
                if (isset($parseItemComments['BeConfigItem'][0])) {
                    $annotation = new BeConfigItem($parseItemComments['BeConfigItem'][0]);
                    $configItem = $annotation->toArray();
                    if (isset($configItem['value'])) {
                        $configItem['label'] = $configItem['value'];
                        unset($configItem['value']);
                    }

                    $configItem['name'] = $itemName;
                    $configItem['value'] = $configInstance->$itemName;

                    $driverClass = null;
                    if (isset($configItem['driver'])) {
                        if (substr($configItem['driver'], 0, 8) == 'FormItem') {
                            $driverClass = '\\Be\\Mf\\Plugin\\Form\\Item\\' . $configItem['driver'];
                        } else {
                            $driverClass = $configItem['driver'];
                        }
                    } else {
                        $driverClass = \Be\Mf\Plugin\Form\Item\FormItemInput::class;
                    }
                    $driver = new $driverClass($configItem);

                    $configItemDrivers[] = $driver;
                }
            }
        }
        $response->set('configItemDrivers', $configItemDrivers);

        $theme = null;
        if (isset($this->setting['theme'])) {
            $theme = $this->setting['theme'];
        }
        $response->display('Plugin.Config.display', $theme);
    }


    public function saveConfig()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        try {
            $appName = isset($this->setting['appName']) ? $this->setting['appName'] : $request->getAppName();
            $configName = $request->get('configName', '');
            if (!$configName) {
                throw new PluginException('参数（configName）缺失！');
            }

            $postData = $request->json();
            $formData = $postData['formData'];

            $code = "<?php\n";
            $code .= 'namespace Be\\Mf\\Data\\' . $appName . '\\Config;' . "\n\n";
            $code .= 'class ' . $configName . "\n";
            $code .= "{\n";

            $className = '\\Be\\Mf\\App\\' . $appName . '\\Config\\' . $configName;
            if (!class_exists($className)) {
                throw new PluginException('配置项（' . $className . '）不存在！');
            }

            $newValues = [];
            $newValueStrings = [];
            $reflection = new \ReflectionClass($className);
            $properties = $reflection->getProperties(\ReflectionMethod::IS_PUBLIC);
            foreach ($properties as $property) {
                $itemName = $property->getName();
                if (!isset($formData[$itemName])) {
                    throw new PluginException('参数 (' . $itemName . ') 缺失！');
                }

                $itemComment = $property->getDocComment();
                $parseItemComments = \Be\F\Util\Annotation::parse($itemComment);
                if (isset($parseItemComments['BeConfigItem'][0])) {
                    $annotation = new BeConfigItem($parseItemComments['BeConfigItem'][0]);
                    $configItem = $annotation->toArray();
                    if (isset($configItem['value'])) {
                        $configItem['label'] = $configItem['value'];
                        unset($configItem['value']);
                    }

                    $configItem['name'] = $itemName;
                    $configItems[] = $configItem;

                    $driverClass = null;
                    if (isset($configItem['driver'])) {
                        if (substr($configItem['driver'], 0, 8) == 'FormItem') {
                            $driverClass = '\\Be\\Mf\\Plugin\\Form\\Item\\' . $configItem['driver'];
                        } else {
                            $driverClass = $configItem['driver'];
                        }
                    } else {
                        $driverClass = \Be\Mf\Plugin\Form\Item\FormItemInput::class;
                    }
                    $driver = new $driverClass($configItem);
                    $driver->submit($formData);

                    $newValues[$itemName] = $driver->newValue;
                    $newValueString = null;
                    switch ($driver->valueType) {
                        case 'array(int)':
                        case 'array(float)':
                        $newValueString = '[' . implode(',', $driver->newValue) . ']';
                            break;
                        case 'array':
                        case 'array(string)':
                        $newValueString = $driver->newValue;
                            foreach ($newValueString as &$x) {
                                $x = str_replace('\'', '\\\'', $x);
                            }
                            unset($x);
                        $newValueString = '[\'' . implode('\',\'', $newValueString) . '\']';
                            break;
                        case 'mixed':
                            $newValueString = var_export($driver->newValue, true);
                            break;
                        case 'bool':
                            $newValueString = $driver->newValue ? 'true' : 'false';
                            break;
                        case 'int':
                        case 'float':
                            $newValueString = $driver->newValue;
                            break;
                        case 'string':
                            $newValueString = '\'' . str_replace('\'', '\\\'', $driver->newValue) . '\'';
                            break;
                        default:
                            $newValueString = var_export($driver->newValue, true);
                    }

                    $newValueStrings[$itemName] = $newValueString;
                }
            }

            $instance = Be::getConfig($appName . '.' . $configName);
            $vars = get_object_vars($instance);
            foreach ($vars as $k => $v) {
                if (isset($newValueStrings[$k])) {
                    $code .= '  public $' . $k . ' = ' . $newValueStrings[$k] . ';' . "\n";
                } else {
                    $code .= '  public $' . $k . ' = ' . var_export($v, true) . ';' . "\n";
                }
            }

            $code .= "}\n";

            $path = Be::getRuntime()->getDataPath() . '/' . $appName . '/Config/' . $configName . '.php';
            $dir = dirname($path);
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            file_put_contents($path, $code, LOCK_EX);
            chmod($path, 0755);

            // 更新 config 实例
            foreach ($vars as $k => $v) {
                if (isset($newValues[$k])) {
                    $instance->$k = $newValues[$k];
                }
            }

            $response->success('保存成功，系统将自动重载！');

            // 重启系统
            Be::getRuntime()->reload();

        } catch (\Throwable $t) {
            $response->error('保存失败：' . $t->getMessage());
            Be::getLog()->error($t);
        }
    }

    public function resetConfig()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();
        try {
            $appName = isset($this->setting['appName']) ? $this->setting['appName'] : $request->getAppName();
            $configName = $request->get('configName', '');
            if (!$configName) {
                throw new PluginException('参数（configName）缺失！');
            }

            $path = Be::getRuntime()->getDataPath() . '/' . $appName . '/Config/' . $configName . '.php';
            if (file_exists($path)) @unlink($path);

            // 更新 config 实例
            $config = Be::getConfig($appName . '.' . $configName);

            $class = '\\Be\\' .  Be::getRuntime()->getFrameworkName() .'\\App\\' . $appName . '\\Config\\' . $configName;
            $newConfig = new $class();

            $vars = get_object_vars($newConfig);
            foreach ($vars as $k => $v) {
                if (isset($config->$k)) {
                    $config->$k = $v;
                }
            }

            $vars = get_object_vars($config);
            foreach ($vars as $k => $v) {
                if (!isset($newConfig->$k)) {
                    unset($config->$k);
                }
            }

            $response->success('恢复默认值成功，系统将自动重载！');

            // 重启系统
            Be::getRuntime()->reload();

        } catch (\Throwable $t) {
            $response->error('恢复默认值失败：' . $t->getMessage());
            Be::getLog()->error($t);
        }
    }

}

