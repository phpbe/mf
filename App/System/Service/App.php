<?php
namespace Be\App\System\Service;

use Be\System\Be;
use Be\System\Service\ServiceException;

class App extends \Be\System\Service
{

	private $apps = null;

    public function getApps()
    {
		if ($this->apps == null) {
			$this->apps = Be::getDb()->withCache(600)->getKeyObjects('SELECT * FROM system_installed_app', null, 'name');
		}

        return $this->apps;
    }

    public function getAppNames()
    {
        return array_keys($this->getApps());
    }

	public function getAppCount()
    {
		return count($this->getApps());
    }

    public function getAppNameLabelKeyValues()
    {
        return array_column($this->getApps(), 'label', 'name');
    }
    
    // 安装应用文件
    public function install($app)
    {

        $db = Be::getDb();
        $exist = $db->getObject('SELECT * FROM system_installed_app WHERE `name`=\''.$app.'\'');
        if ($exist) {
            throw new ServiceException('应用已于'.$exist->install_time.'安装过！');
        }

        $class = '\\Be\\App\\'.$app.'\\Installer';
        if (class_exists($class)) {
            $installer = new $class();
            $installer->install();
        }

        $property = Be::getProperty('App.'.$app);
        $db->insert('system_installed_app', [
            'name' => $property->name,
            'label' => $property->label,
            'icon' => $property->icon,
            'install_time' => date('Y-m-d H:i:s')
        ]);

		return true;
    }
    

    // 删除应用
    public function uninstall($app)
    {
        $db = Be::getDb();
        $exist = $db->getObject('SELECT * FROM system_installed_app WHERE `name`=\''.$app.'\'');
        if (!$exist) {
            throw new ServiceException('该应用尚未安装！');
        }

        $class = '\\Be\\App\\'.$app.'\\UnInstaller';
        if (class_exists($class)) {
            $installer = new $class();
            $installer->uninstall();
        }

        $db->query('DELETE FROM system_installed_app WHERE `name`=\''.$app.'\'')->closeCursor();
        return true;
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
