<?php

namespace Be\App\System\Service;

use Be\App\System\Helper\DocComment;
use Be\System\Annotation\BeConfig;
use Be\System\Annotation\BeConfigItem;
use Be\System\Be;
use Be\System\Service;
use Be\System\Exception\ServiceException;

class Config extends Service
{

    private $configTree = null;

    /**
     * 获取配置文件树状结构
     */
    public function getConfigTree()
    {
        if ($this->configTree !== null) return $this->configTree;
        $appNames = Be::getService('System.App')->getApps();

        $configTree = [];
        foreach ($appNames as $app) {
            $dir = Be::getRuntime()->getRootPath() . Be::getProperty('App.' . $app->name)->getPath() . '/Config';
            $configs = array();
            if (file_exists($dir) && is_dir($dir)) {
                $fileNames = scandir($dir);
                foreach ($fileNames as $fileName) {
                    if ($fileName != '.' && $fileName != '..' && is_file($dir . '/' . $fileName)) {
                        $configName = substr($fileName, 0, -4);
                        $configSummary = $this->getConfigSummary($app->name, $configName);
                        if ($configSummary) {
                            $configs[] = $configSummary;
                        }
                    }
                }
            }

            if (count($configs)) {
                $configTree[] = [
                    'app' => $app,
                    'configs' => $configs
                ];
            }
        }

        $this->configTree = $configTree;
        return $configTree;
    }


    private $configSummaries = [];

    /**
     * 获取配置信息摘要（不包含配置项信息）
     *
     * @param string $appName 应用名
     * @param string $configName 配置文件名
     * @return array | false
     */
    public function getConfigSummary($appName, $configName)
    {
        $className = '\\Be\\App\\' . $appName . '\\Config\\' . $configName;
        if (isset($this->configSummaries[$className])) {
            return $this->configSummaries[$className];
        }

        $this->configSummaries[$className] = false;
        if (class_exists($className)) {
            $reflection = $this->getReflectionClass($className);
            $classComment = $reflection->getDocComment();
            $parseClassComments = DocComment::parse($classComment);
            if (isset($parseClassComments['BeConfig'][0])) {
                $this->configSummaries[$className] = [
                    'appName' => $appName,
                    'configName' => $configName,
                    'annotation' => new BeConfig($parseClassComments['BeConfig'][0])
                ];
            }
        }

        return $this->configSummaries[$className];
    }


    private $configs = [];

    /**
     * 获取配置信息
     *
     * @param string $appName 应用名
     * @param string $configName 配置文件名
     * @return array
     */
    public function getConfig($appName, $configName)
    {
        $className = '\\Be\\App\\' . $appName . '\\Config\\' . $configName;
        if (isset($this->configs[$className])) {
            return $this->configs[$className];
        }

        $config = false;
        if (class_exists($className)) {
            $reflection = $this->getReflectionClass($className);
            $classComment = $reflection->getDocComment();
            $parseClassComments = DocComment::parse($classComment);
            if (isset($parseClassComments['BeConfig'][0])) {
                $config = [
                    'appName' => $appName,
                    'configName' => $configName,
                    'annotation' => new BeConfig($parseClassComments['BeConfig'][0])
                ];

                $configInstance = Be::getConfig($appName . '.' . $configName);

                $configItems = [];
                $properties = $reflection->getProperties(\ReflectionMethod::IS_PUBLIC);
                foreach ($properties as &$property) {
                    $itemName = $property->getName();
                    $itemComment = $property->getDocComment();
                    $parseItemComments = DocComment::parse($itemComment);
                    if (isset($parseItemComments['BeConfigItem'][0])) {
                        $annotation = new BeConfigItem($parseItemComments['BeConfigItem'][0]);

                        $driverClass = $annotation->driver;
                        if (!$driverClass) {
                            $driverClass = \Be\Plugin\Config\Item\ConfigItemInput::class;
                        } else {
                            if (substr($driverClass, 0, 10) == 'ConfigItem') {
                                $driverClass = '\\Be\\Plugin\\Config\\Item\\' . $driverClass;
                            }
                        }

                        $configItemDriver = new $driverClass($itemName, $configInstance->$itemName, $annotation);
                        $configItemDriver->appName = $appName;
                        $configItemDriver->configName = $configName;

                        $configItem = [
                            'appName' => $appName,
                            'configName' => $configName,
                            'itemName' => $itemName,
                            'annotation' => $annotation,
                            'driver' => $configItemDriver
                        ];
                        $configItems[$itemName] = $configItem;
                    }
                }
                $config['items'] = $configItems;
            }
        }

        $this->configs[$className] = $config;
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
        $class = '\\Be\\App\\' . $appName . '\\Config\\' . $configName;
        $instance = new $class();
        $vars = get_object_vars($instance);

        $config = $this->getConfig($appName, $configName);

        $code = "<?php\n";
        $code .= 'namespace Be\\Data\\System\\Config\\' . $appName . ';' . "\n\n";
        $code .= 'class ' . $configName . "\n";
        $code .= "{\n";

        foreach ($vars as $k => $v) {
             if (isset($config['items'][$k])) {
                $configItem = $config['items'][$k];
                $driver = $configItem['driver'];
                if (!isset($data[$k])) {
                    throw new ServiceException('参数 ' . $driver->label . ' (' . $k . ') 缺失！');
                }
                $driver->submit($data);
                $code .= '  public $' . $k . ' = ' . var_export($driver->newValue, true) . ';' . "\n";
            } else {
                $code .= '  public $' . $k . ' = ' . var_export($v, true) . ';' . "\n";
            }
        }

        $code .= "}\n";

        $path = Be::getRuntime()->getDataPath() . '/System/Config/' . $appName . '/' . $configName . '.php';
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
        $path = Be::getRuntime()->getDataPath() . '/System/Config/' . $appName . '/' . $configName . '.php';
        if (file_exists($path)) @unlink($path);
        return true;
    }


    private $reflectionClass = [];
    private function getReflectionClass($className)
    {
        if (isset($this->reflectionClass[$className])) return $this->reflectionClass[$className];

        if (class_exists($className)) {
            $this->reflectionClass[$className] = new \ReflectionClass($className);
            return $this->reflectionClass[$className];
        }

        return null;
    }

}






