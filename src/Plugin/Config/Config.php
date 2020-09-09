<?php

namespace Be\Plugin\Config;

use Be\App\System\Helper\DocComment;
use Be\System\Annotation\BeConfig;
use Be\System\Annotation\BeConfigItem;
use Be\System\Be;
use Be\System\Exception\PluginException;
use Be\System\Plugin;
use Be\System\Request;
use Be\System\Response;

/**
 * 配置
 *
 * Class Config
 * @package Be\Plugin
 */
class Config extends Plugin
{

    public function display()
    {
        $appName = isset($this->setting['appName']) ? $this->setting['appName'] : Be::getRuntime()->getAppName();

        $configs = [];
        $dir = Be::getRuntime()->getRootPath() . Be::getProperty('App.' . $appName)->getPath() . '/Config';
        if (file_exists($dir) && is_dir($dir)) {
            $fileNames = scandir($dir);
            foreach ($fileNames as $fileName) {
                if ($fileName != '.' && $fileName != '..' && is_file($dir . '/' . $fileName)) {
                    $configName = substr($fileName, 0, -4);
                    $className = '\\Be\\App\\' . $appName . '\\Config\\' . $configName;
                    if (class_exists($className)) {
                        $reflection = new \ReflectionClass($className);
                        $classComment = $reflection->getDocComment();
                        $parseClassComments = DocComment::parse($classComment);
                        if (isset($parseClassComments['BeConfig'][0])) {
                            $annotation = new BeConfig($parseClassComments['BeConfig'][0]);
                            $config = $annotation->toArray();
                            if (isset($config['value'])) {
                                $config['label'] = $config['value'];
                                unset($config['value']);
                            }
                            $config['name'] = $configName;
                            $config['url'] = beUrl(Be::getRuntime()->getPathway(), ['configName' => $configName]);
                            $configs[] = $config;
                        }
                    }
                }
            }
        }
        Response::set('configs', $configs);

        $configName = Request::get('configName', '');
        if (!$configName) {
            $configName = $configs[0]['name'];
        }
        Response::set('configName', $configName);

        $configItemDrivers = [];
        $className = '\\Be\\App\\' . $appName . '\\Config\\' . $configName;
        if (class_exists($className)) {
            $configInstance = Be::getConfig($appName . '.' . $configName);
            $reflection = new \ReflectionClass($className);
            $properties = $reflection->getProperties(\ReflectionMethod::IS_PUBLIC);
            foreach ($properties as $property) {
                $itemName = $property->getName();
                $itemComment = $property->getDocComment();
                $parseItemComments = DocComment::parse($itemComment);
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
                            $driverClass = '\\Be\\Plugin\\Form\\Item\\' . $configItem['driver'];
                        } else {
                            $driverClass = $configItem['driver'];
                        }
                    } else {
                        $driverClass = \Be\Plugin\Form\Item\FormItemInput::class;
                    }
                    $driver = new $driverClass($configItem);

                    $configItemDrivers[] = $driver;
                }
            }
        }
        Response::set('configItemDrivers', $configItemDrivers);

        $theme = null;
        if (isset($this->setting['theme'])) {
            $theme = $this->setting['theme'];
        }
        Response::display('Plugin.Config.display', $theme);
    }


    public function saveConfig()
    {
        try {
            $appName = isset($this->setting['appName']) ? $this->setting['appName'] : Be::getRuntime()->getAppName();
            $configName = Request::get('configName', '');
            if (!$configName) {
                throw new PluginException('参数（configName）缺失！');
            }

            $postData = Request::json();
            $formData = $postData['formData'];

            $code = "<?php\n";
            $code .= 'namespace Be\\Data\\' . $appName . '\\Config;' . "\n\n";
            $code .= 'class ' . $configName . "\n";
            $code .= "{\n";

            $className = '\\Be\\App\\' . $appName . '\\Config\\' . $configName;
            if (!class_exists($className)) {
                throw new PluginException('配置项（' . $className . '）不存在！');
            }

            $newValues = [];
            $reflection = new \ReflectionClass($className);
            $properties = $reflection->getProperties(\ReflectionMethod::IS_PUBLIC);
            foreach ($properties as $property) {
                $itemName = $property->getName();
                if (!isset($formData[$itemName])) {
                    throw new PluginException('参数 (' . $itemName . ') 缺失！');
                }

                $itemComment = $property->getDocComment();
                $parseItemComments = DocComment::parse($itemComment);
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
                            $driverClass = '\\Be\\Plugin\\Form\\Item\\' . $configItem['driver'];
                        } else {
                            $driverClass = $configItem['driver'];
                        }
                    } else {
                        $driverClass = \Be\Plugin\Form\Item\FormItemInput::class;
                    }
                    $driver = new $driverClass($configItem);
                    $driver->submit($formData);

                    $newValue = null;
                    switch ($driver->valueType) {
                        case 'array(int)':
                        case 'array(float)':
                            $newValue = '[' . implode(',', $driver->newValue) . ']';
                            break;
                        case 'array':
                        case 'array(string)':
                            $newValue = $driver->newValue;
                            foreach ($newValue as &$x) {
                                $x = str_replace('\'', '\\\'', $x);
                            }
                            unset($x);
                            $newValue = '[\'' . implode('\',\'', $newValue) . '\']';
                            break;
                        case 'mixed':
                            $newValue = var_export($driver->newValue, true);
                            break;
                        case 'bool':
                            $newValue = $driver->newValue ? 'true' : 'false';
                            break;
                        case 'int':
                        case 'float':
                            $newValue = $driver->newValue;
                            break;
                        case 'string':
                            $newValue = '\'' . str_replace('\'', '\\\'', $driver->newValue) . '\'';
                            break;
                        default:
                            $newValue = var_export($driver->newValue, true);
                    }

                    $newValues[$itemName] = $newValue;
                }
            }

            $instance = Be::getConfig($appName . '.' . $configName);
            $vars = get_object_vars($instance);
            foreach ($vars as $k => $v) {
                if (isset($newValues[$k])) {
                    $code .= '  public $' . $k . ' = ' . $newValues[$k] . ';' . "\n";
                } else {
                    $code .= '  public $' . $k . ' = ' . var_export($v, true) . ';' . "\n";
                }
            }

            $code .= "}\n";

            $path = Be::getRuntime()->getDataPath() . '/' . $appName . '/Config/' . $configName . '.php';
            file_put_contents($path, $code, LOCK_EX);

            Response::success('保存成功！');
        } catch (\Exception $e) {
            Response::error('保存失败：' . $e->getMessage());
        }
    }

    public function resetConfig()
    {
        try {
            $appName = isset($this->setting['appName']) ? $this->setting['appName'] : Be::getRuntime()->getAppName();
            $configName = Request::get('configName', '');
            if (!$configName) {
                throw new PluginException('参数（configName）缺失！');
            }

            $path = Be::getRuntime()->getDataPath() . '/' . $appName . '/Config/' . $configName . '.php';
            if (file_exists($path)) @unlink($path);

            Response::success('恢复默认值成功！');
        } catch (\Exception $e) {
            Response::error('恢复默认值失败：' . $e->getMessage());
        }
    }

}

