<?php

namespace Be\System;


use Be\System\Exception\RuntimeException;

/**
 *  运行时
 * @package System
 *
 */
class Runtime
{
    private $rootPath = null;

    private $rootUrl = null;

    private $dataDir = 'data';

    private $cacheDir = 'cache';

    private $appName = null;
    private $controllerName = null;
    private $actionName = null;
    private $pathway = null;

    public function __construct()
    {
    }

    /**
     * 设置BE框架的根路径
     *
     * @param string $rootPath BE框架的根路径，绝对路径
     */
    public function setRootPath($rootPath)
    {
        $this->rootPath = $rootPath;
    }

    /**
     * 获取BE框架的根路径
     *
     * @return string
     */
    public function getRootPath()
    {
        return $this->rootPath;
    }

    /**
     * @return string
     */
    public function getCachePath()
    {
        return $this->rootPath . '/' . $this->cacheDir;
    }

    /**
     * @return string
     */
    public function getDataPath()
    {
        return $this->rootPath . '/' . $this->dataDir;
    }


    /**
     * @return string
     */
    public function getRootUrl()
    {
        if ($this->rootUrl === null) {
            $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
            $url .= isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ($_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT']));
            $url .= substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/index.php'));

            $this->rootUrl = $url;
        }

        return $this->rootUrl;
    }

    /**
     * @return string
     */
    public function getDataUrl()
    {
        return $this->getRootUrl() . '/' . $this->dataDir;
    }

    /**
     * @param string $dataDir
     */
    public function setDataDir($dataDir)
    {
        $this->dataDir = $dataDir;
    }

    /**
     * @return string
     */
    public function getDataDir()
    {
        return $this->dataDir;
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return $this->cacheDir;
    }

    /**
     * @param string $cacheDir
     */
    public function setCacheDir($cacheDir)
    {
        $this->cacheDir = $cacheDir;
    }

    /**
     * 获取当前执行的 APP 名
     *
     * @return null | string
     */
    public function getAppName()
    {
        return $this->appName;
    }

    /**
     * 获取当前执行的 控制器 名
     *
     * @return null | string
     */
    public function getControllerName()
    {
        return $this->controllerName;
    }

    /**
     * 获取当前执行的 动作 名
     *
     * @return null | string
     */
    public function getActionName()
    {
        return $this->actionName;
    }

    /**
     * 获取当前执行的 路径（应用名.控制器名.动作名）
     *
     * @return null | string
     */
    public function getPathway()
    {
        return $this->pathway;
    }

    public function execute()
    {
        try {
            // 检查网站配置， 是否暂停服务
            $configSystem = Be::getConfig('System.System');

            // 默认时区
            date_default_timezone_set($configSystem->timezone);

            // 启动 session
            Session::start();

            // 从网址中提取出 路径
            $pathway = null;
            if ($configSystem->urlRewrite) {

                //print_r($_SERVER);

                /*
                 * REQUEST_URI 可能值为：[/path]/{action}[/{k-v}].html?[k=v]
                 * 需要解析的有效部分为： {action}[/{k-v}]
                 */
                $uri = $_SERVER['REQUEST_URI'];    // 返回值为:

                // 移除 [/path]
                $scriptName = $_SERVER['SCRIPT_NAME'];
                $indexName = '/index.php';
                $pos = strrpos($scriptName, $indexName);
                if ($pos !== false) {
                    $path = substr($scriptName, 0, $pos);
                    if ($path) {
                        if (strpos($uri, $path) === 0) {
                            $uri = substr($uri, strlen($path));
                        }
                    }
                }

                // 移除 ?[k=v]
                if ($_SERVER['QUERY_STRING'] != ''){
                    $uri = substr($uri, 0, strrpos($uri, '?'));
                }

                // 移除 .html
                $lenSefSuffix = strlen($configSystem->urlSuffix);
                if (substr($uri, -$lenSefSuffix, $lenSefSuffix) == $configSystem->urlSuffix) {
                    $uri = substr($uri, 0, strrpos($uri, $configSystem->urlSuffix));
                }

                // 移除结尾的 /
                if (substr($uri, -1, 1) == '/') $uri = substr($uri, 0, -1);

                // /{action}[/{k-v}]
                $uris = explode('/', $uri);
                $len = count($uris);
                if ($len > 1) {
                    $pathway = $uris[1];
                }

                if ($len > 2) {
                    /**
                     * 把网址按以下规则匹配
                     * /{action}/{参数名1}-{参数值1}/{参数名2}-{参数值2}/{参数名3}-{参数值3}
                     * 其中{参数名}-{参数值} 值对不限数量
                     */
                    for ($i = 2; $i < $len; $i++) {
                        $pos = strpos($uris[$i], '-');
                        if ($pos !== false) {
                            $key = substr($uris[$i], 0, $pos);
                            $val = substr($uris[$i], $pos + 1);

                            $_GET[$key] = $_REQUEST[$key] = $val;
                        }
                    }
                }

            } else {
                $pathway = Request::request('pathway', '');
            }

            $appName = null;
            $controllerName = null;
            $actionName = null;
            if ($pathway) {
                $pathways = explode('.', $pathway);
                if (count($pathways) == 3) {
                    $appName = $pathways[0];
                    $controllerName = $pathways[1];
                    $actionName = $pathways[2];
                }
            }

            // 默认访问控制台页面
            if (!$appName) {
                $appName = 'System';
                $controllerName = 'System';
                $actionName = 'dashboard';
            }

            $this->appName = $appName;
            $this->controllerName = $controllerName;
            $this->actionName = $actionName;
            $this->pathway = $appName . '.' . $controllerName . '.' . $actionName;

            $my = Be::getUser();
            if ($my->id == 0) {
                Be::getService('System.User')->rememberMe();
                $my = Be::getUser();
            }

            // 校验权限
            $role0 = Be::getRole(0);
            if (!$role0->hasPermission($appName, $controllerName, $actionName)) {
                // 访问的不是公共内容，且未登录，跳转到登录页面
                if ($my->id == 0) {
                    $return = Request::get('return', base64_encode(Request::url()));
                    Response::redirect(beUrl('System.User.login', ['return' => $return]));
                } else {
                    if (!$my->hasPermission($appName, $controllerName, $actionName)) {
                        Response::set('code', -1024);
                        Response::error('您没有权限操作该功能！');
                    }
                }
            }

            if ($my->id > 0) {
                // 已登录用户，IP锁定功能校验
                $configUser = Be::getConfig('System.User');
                if ($configUser->ipLock) {
                    if ($my->last_login_ip != Request::ip()) {
                        Be::getService('System.User')->logout();
                        Response::error('检测到您的账号在其它地点登录！', beUrl('System.User.login'));
                    }
                }
            }

            $class = 'Be\\App\\' . $appName . '\\Controller\\' . $controllerName;
            if (!class_exists($class)) {
                Response::set('code', -404);
                Response::error('控制器 ' . $appName . '/' . $controllerName . ' 不存在！');
            }

            $instance = new $class();
            if (method_exists($instance, $actionName)) {
                $instance->$actionName();
            } else {
                Response::set('code', -404);
                Response::error('未定义的任务：' . $actionName);
            }

        } catch (\Throwable $e) {
            $hash = md5(json_encode([
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'message' => $e->getMessage()
            ]));

            RuntimeLog::emergency($e->getMessage(), [
                'hash' => $hash,
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace()
            ]);

            Response::set('logHash', $hash);
            Response::exception($e);
        }
    }
}
