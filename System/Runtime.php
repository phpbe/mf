<?php

namespace Be\System;

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

    private $adminDir = 'admin';

    private $backend = false;

    private $appName = null;
    private $controllerName = null;
    private $actionName = null;

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
     * @return string
     */
    public function getAdminUrl()
    {
        return $this->getRootUrl() . '/' . $this->adminDir;
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
     * 设置后台功能目录
     * 该目录仅在网址中出现，不需要创建
     *
     * @param string $adminDir
     */
    public function setAdminDir($adminDir)
    {
        $this->adminDir = $adminDir;
    }

    /**
     * 获取后台路径
     *
     * @return string
     */
    public function getAdminDir()
    {
        return $this->adminDir;
    }

    /**
     * 当前访问的功能是否为后台功能
     * @return bool
     */
    public function isBackend()
    {
        return $this->backend;
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

    public function execute()
    {

        // 检查网站配置， 是否暂停服务
        $configSystem = Be::getConfig('System.System');
        if ($configSystem->offline) Response::end($configSystem->offlineMessage);

        // 默认时区
        date_default_timezone_set($configSystem->timezone);

        try {

            // 启动 session
            Session::start();

            // 从网址中提取出 action
            $action = null;
            if ($configSystem->sef) {

                //print_r($_SERVER);

                /*
                 * REQUEST_URI 可能值为：[/path][/admin]/{action}[/{k-v}].html?[k=v]
                 * 需要解析的有效部分为： {app}.{controller}.{action}[/{k-v}]
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

                // 是否为后台功能 移除[/admin]
                if (substr($uri, 0, strlen($this->adminDir) + 1) == '/' . $this->adminDir) {
                    $this->backend = true; // 后台功能
                    $uri = substr($uri, strlen($this->adminDir) + 1);
                }

                // 移除 ?[k=v]
                if ($_SERVER['QUERY_STRING'] != ''){
                    $uri = substr($uri, 0, strrpos($uri, '?'));
                }

                // 移除 .html
                $lenSefSuffix = strlen($configSystem->sefSuffix);
                if (substr($uri, -$lenSefSuffix, $lenSefSuffix) == $configSystem->sefSuffix) {
                    $uri = substr($uri, 0, strrpos($uri, $configSystem->sefSuffix));
                }

                // 移除结尾的 /
                if (substr($uri, -1, 1) == '/') $uri = substr($uri, 0, -1);

                // /{action}[/{k-v}]
                $uris = explode('/', $uri);
                $len = count($uris);
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
                $phpSelf = $_SERVER['PHP_SELF'];

                // 是否为后台功能
                if (substr($phpSelf, strrpos($phpSelf, '/') == $this->adminDir)) {
                    $this->backend = true; // 后台功能
                }

                $action = Request::request('action', '');
            }

            $appName = null;
            $controllerName = null;
            $actionName = null;
            if ($action) {
                $actions = explode('.', $action);
                if (count($actions) == 3) {
                    $appName = $actions[0];
                    $controllerName = $actions[1];
                    $actionName = $actions[2];
                }
            }

            // 后台默认访问控制台页面
            if (!$appName) {
                if ($this->backend) {
                    $appName = 'System';
                    $controllerName = 'System';
                    $actionName = 'dashboard';
                } else {
                    // 默认首页时
                    if (!$appName) {
                        $homeParams = $configSystem->homeParams;
                        foreach ($homeParams as $key => $val) {
                            $_GET[$key] = $_REQUEST[$key] = $val;
                            if ($key == 'app') {
                                $appName = $val;
                            }  elseif ($key == 'controller') {
                                $controllerName = $val;
                            } elseif ($key == 'action') {
                                $actionName = $val;
                            }
                        }
                    }
                }
            }

            $this->appName = $appName;
            $this->controllerName = $controllerName;
            $this->actionName = $actionName;

            if ($this->backend) {

                $my = Be::getAdminUser();
                if ($my->isGuest()) {
                    Be::getService('System.AdminUser')->rememberMe();
                    $my = Be::getAdminUser();

                    if ($my->isGuest()) {
                        if ($appName != 'System' || $controllerName != 'AdminUser' || $actionName != 'login') {
                            $return = Request::get('return', base64_encode(Request::url()));
                            Response::redirect(adminUrl('System.AdminUser.login', ['return' => $return]));
                        }
                    }
                }


                $class = 'App\\' . $appName . '\\AdminController\\' . $controllerName;
                if (!class_exists($class)) throw new RuntimeException('控制器 ' . $appName . '/' . $controllerName . ' 不存在！', -404);
                $instance = new $class();
                if (method_exists($instance, $actionName)) {

                    if ($appName != 'System' || $controllerName != 'AdminUser' || $actionName != 'login') {
                        if (!$my->hasPermission($appName, $controllerName, $actionName)) {
                            Response::error('您没有权限操作该后台功能！', -1024);
                        }
                    }

                    $instance->$actionName();

                } else {
                    throw new RuntimeException('未定义的后台任务：' . $actionName, -404);
                }

            } else {

                $my = Be::getUser();
                if ($my->id == 0) {
                    Be::getService('System.User')->rememberMe();
                    $my = Be::getUser();
                }

                $class = 'App\\' . $appName . '\\Controller\\' . $controllerName;
                if (!class_exists($class)) throw new RuntimeException('控制器 ' . $appName . '/' . $controllerName . ' 不存在！', -404);
                $instance = new $class();
                if (method_exists($instance, $actionName)) {

                    if (!$my->hasPermission($appName, $controllerName, $actionName)) {
                        Response::error('您没有权限操作该功能！',  -1024);
                    }

                    $instance->$actionName();

                } else {
                    throw new RuntimeException('未定义的任务：' . $actionName,  -404);
                }
            }

        } catch (\Throwable $e) {

            Log::emergency($e->getMessage(), [
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace()
            ]);


            $db = Be::getDb();
            if ($db->inTransaction()) $db->rollback();

            Response::exception($e);
        }

    }
}
