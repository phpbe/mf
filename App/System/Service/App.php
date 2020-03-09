<?php
namespace Be\App\System\Service;

use Be\System\Be;

class App extends \Be\System\Service
{

    private $beApi = 'http://api.phpbe.com/';

    private $appTables = null;
	private $apps = null;

    public function getApps()
    {
		if ($this->apps == null) {
			$apps = array();

            $dir = Be::getRuntime()->getRootPath() . '/App';
            if (file_exists($dir) && is_dir($dir)) {
                $fileNames = scandir($dir);
                foreach ($fileNames as $fileName) {
                    if ($fileName != '.' && $fileName != '..' && is_dir($dir . '/' . $fileName)) {
                        $apps[] = Be::getApp($fileName);
                    }
                }
            }

			$this->apps = $apps;
		}

        return $this->apps;
    }

    public function getAppNames()
    {
        $appNames = array();

        $dir = Be::getRuntime()->getRootPath() . '/App';
        if (file_exists($dir) && is_dir($dir)) {
            $fileNames = scandir($dir);
            foreach ($fileNames as $fileName) {
                if ($fileName != '.' && $fileName != '..' && is_dir($dir . '/' . $fileName)) {
                    $appNames[] = $fileName;
                }
            }
        }

        return $appNames;
    }

	public function getAppCount()
    {
		return count($this->getApps());
    }
    
    public function getRemoteApps($option = array())
    {
        $libHttp = Be::getLib('Http');
        $Response = $libHttp->post($this->beApi . 'apps/', $option);
        
        $apps = json_decode($Response);

        return $apps;
    }
        
    public function getRemoteApp($appId)
    {
        $libHttp = Be::getLib('Http');
        $Response = $libHttp->get($this->beApi . 'app/' . $appId);
        
        $app = json_decode($Response);

		return $app;
    }
    
    
    // 安装应用文件
    public function install($app)
    {
        $libHttp = Be::getLib('Http');
        $Response = $libHttp->get($this->beApi . 'appDownload/'.$app->version->id.'/');

		$zip = Be::getRuntime()->getDataPath().'/system/tmp/app_'.$app->name.'.zip';
        file_put_contents($zip, $Response);

		$dir = Be::getRuntime()->getDataPath().'/system/tmp/app_'.$app->name;
        $libZip = Be::getLib('zip');
        $libZip->open($zip);
        if (!$libZip->extractTo($dir)) {
            $this->setError($libZip->getError());
            return false;
        }

		include PATH_ADMIN.'/system/app.php';
		include $dir.'/admin/apps/'.$app->name.'.php';
		
		$appClass = 'app_'.$app->name;
		$appObj = new $appClass();
		$appObj->setName($app->name);
		$appObj->install();

		$adminConfigSystem = Be::getConfig('System', 'admin');
        $serviceSystem = Be::getService('system');
		if (!in_array($app->name, $adminConfigSystem->apps)) {
			$adminConfigSystem->apps[] = $app->name;
            $serviceSystem->updateConfig($adminConfigSystem, Be::getRuntime()->getDataPath().'/adminConfig/system.php');
		}

		// 删除临时文件
		unlink($zip);

		$libFso = Be::getLib('fso');
		$libFso->rmDir($dir);

		return true;
    }
    

    // 删除应用
    public function uninstall($name)
    {
		Be::getApp($name)->uninstall();
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
        $result = [];

        $className = 'App\\' . $app . '\\Config\\' . $config;
        $reflection = new \ReflectionClass($className);

        // 类注释
        $docComment = $reflection->getDocComment();
        $result['class'] = $this->parseDocComment($docComment);

        // 属性注释
        $result['properties'] = [];
        $properties = $reflection->getProperties(\ReflectionMethod::IS_PUBLIC);
        foreach ($properties as &$property) {
            $docComment = $property->getDocComment();
            $result['properties'][$property->getName()] = $this->parseDocComment($docComment);
        }

        // 方法注释
        $result['methods'] = [];
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($methods as &$method) {
            $docComment = $method->getDocComment();
            $result['methods'][$method->getName()] = $this->parseDocComment($docComment);
        }

        return $result;
    }


    /**
     * 解析文档注释
     *
     * @param string $docComment 文档注释
     * @return array
     */
    public function parseDocComment($docComment)
    {
        $result = [];
        if (preg_match('#^/\*\*(.*)\*/#s', $docComment, $comment) === false) return [];
        $comment = trim($comment[1]);

        if (preg_match_all('#^\s*\*(.*)#m', $comment, $lines) === false) return [];
        $lines = $lines[1];

        $description = [];
        foreach ($lines as $line) {

            $line = trim($line);

            if ($line) {
                // 该行注释由 @ 开头
                if (strpos($line, '@') === 0) {
                    if (strpos($line, ' ') > 0) {
                        $param = substr($line, 1, strpos($line, ' ') - 1);
                        $value = substr($line, strlen($param) + 2);
                    } else {
                        $param = substr($line, 1);
                        $value = '';
                    }

                    if ($param == 'param' || $param == 'return') {
                        $pos = strpos($value, ' ');
                        $type = substr($value, 0, $pos);
                        $value = '(' . $type . ')' . substr($value, $pos + 1);
                    } elseif ($param == 'class') {
                        $r = preg_split("[|]", $value);
                        if (is_array($r)) {
                            $param = $r[0];
                            parse_str($r[1], $value);
                            foreach ($value as $key => $val) {
                                $val = explode(',', $val);
                                if (count($val) > 1)
                                    $value[$key] = $val;
                            }
                        } else {
                            $param = 'Unknown';
                        }
                    }

                    if (empty ($result[$param])) {
                        $result[$param] = $value;
                    } else if ($param == 'param') {
                        $arr = array(
                            $result[$param],
                            $value
                        );
                        $result[$param] = $arr;
                    } else {
                        $result[$param] = $value + $result[$param];
                    }

                    if (!isset($result['summary']) && count($description) > 0) {
                        $result['summary'] = implode(PHP_EOL, $description);
                        $description = [];
                    }
                } else {
                    $description[] = $line;
                }
            } else {
                if (!isset($result['summary']) && count($description) > 0) {
                    $result['summary'] = implode(PHP_EOL, $description);
                    $description = [];
                }
            }
        }

        if (count($description) > 0) {
            $description = implode(' ', $description);
            $result['description'] = $description;
        }

        return $result;
    }


}
