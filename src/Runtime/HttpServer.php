<?php

namespace Be\Mf\Runtime;

use Be\F\Config\ConfigFactory;
use Be\F\Db\DbFactory;
use Be\F\Redis\RedisFactory;
use Be\F\Request\RequestFactory;
use Be\F\Response\ResponseFactory;
use Be\F\Runtime\RuntimeFactory;
use Be\Mf\Be;


class HttpServer
{
    /**
     * @var \Swoole\Http\Server
     */
    private $server = null;

    CONST MIME = [
        'html' => 'text/html',
        'htm' => 'text/html',
        'xhtml' => 'application/xhtml+xml',
        'xml' => 'text/xml',
        'txt' => 'text/plain',
        'log' => 'text/plain',

        'js' => 'application/javascript',
        'json' => 'application/json',
        'css' => 'text/css',

        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'png' => 'image/png',
        'bmp' => 'image/bmp',
        'ico' => 'image/icon',
        'svg' => 'image/svg+xml',

        'mp3' => 'audio/mpeg',
        'wav' => 'audio/wav',

        'mp4' => 'video/avi',
        'avi' => 'video/avi',
        '3gp' => 'application/octet-stream',
        'flv' => 'application/octet-stream',
        'swf' => 'application/x-shockwave-flash',

        'zip' => 'application/zip',
        'rar' => 'application/octet-stream',

        'ttf' => 'application/octet-stream',
        'otf' => 'application/octet-stream',
        'eot' => 'application/octet-stream',
        'fon' => 'application/octet-stream',
        'woff' => 'application/octet-stream',
        'woff2' => 'application/octet-stream',

        'doc' => 'application/msword',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        'mdb' => 'application/msaccess',
        'chm' => 'application/octet-stream',

        'pdf' => 'application/pdf',
    ];


    public function __construct()
    {
    }


    public function start()
    {
        if ($this->server !== null) {
            return;
        }

        \Co::set(['hook_flags' => SWOOLE_HOOK_ALL]);

        // 检查网站配置， 是否暂停服务
        $configSystem = Be::getConfig('System.System');
        date_default_timezone_set($configSystem->timezone);

        $this->server = new \Swoole\Http\Server("0.0.0.0", 80);
        $this->server->set(['enable_coroutine' => true]);

        // 初始化数据库，Redis连接池
        DbFactory::init();
        RedisFactory::init();

        $sessionConfig = ConfigFactory::getInstance('System.Session');
        if ($sessionConfig->driver == 'File') {
            $dir = RuntimeFactory::getInstance()->getCachePath() . '/session';
            \Be\F\Util\FileSystem\Dir::rm($dir);
        }

        $this->server->on('request', function ($swRequest, $swResponse) {
            /**
             * @var \Swoole\Http\Response $swResponse
             */
            $swResponse->header('Server', 'be/mf', false);
            $uri = $swRequest->server['request_uri'];

            $ext = strrchr($uri, '.');
            if ($ext) {
                $ext = strtolower(substr($ext, 1));
                if (isset(self::MIME[$ext])) {
                    $rootPath = Be::getRuntime()->getRootPath();
                    if (file_exists($rootPath . $uri)) {
                        $swResponse->header('Content-Type', self::MIME[$ext], false);
                        //缓存
                        $lastModified = gmdate('D, d M Y H:i:s', filemtime($rootPath . $uri)) . ' GMT';
                        if (isset($swRequest->header['if-modified-since']) && $swRequest->header['if-modified-since'] == $lastModified) {
                            $swResponse->status(304);
                            $swResponse->end();
                            return true;
                        }

                        $swResponse->header('Last-Modified', $lastModified, false);

                        //发送Expires头标，设置当前缓存的文档过期时间，GMT格式
                        $swResponse->header('Expires', gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT', false);

                        //发送Cache_Control头标，设置xx秒以后文档过时,可以代替Expires，如果同时出现，max-age优先。
                        $swResponse->header('Cache-Control', 'max-age=31536000', false);
                        $swResponse->header('Pragma', 'max-age=31536000', false);

                        $swResponse->sendfile($rootPath . $uri);
                        return true;
                    }

                    if ($uri == '/favicon.ico') {
                        $swResponse->end();
                        return true;
                    }
                }
            }


            $swRequest->request = null;
            if ($swRequest->get !== null) {
                if ($swRequest->post !== null) {
                    $swRequest->request = array_merge($swRequest->get, $swRequest->post);
                } else {
                    $swRequest->request = $swRequest->get;
                }
            } else {
                if ($swRequest->post !== null) {
                    $swRequest->request = $swRequest->post;
                }
            }

            $request = new \Be\F\Request\Driver($swRequest);
            $response = new \Be\Mf\Response\Driver($swResponse);

            RequestFactory::setInstance($request);
            ResponseFactory::setInstance($response);

            // 启动 session
            Be::getSession()->start();

            try {

                // 检查网站配置， 是否暂停服务
                $configSystem = Be::getConfig('System.System');

                $app = null;
                $controller = null;
                $action = null;

                // 从网址中提取出 路径
                if ($configSystem->urlRewrite) {

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
                    if ($len > 3) {
                        $app = $uris[1];
                        $controller = $uris[2];
                        $action = $uris[3];
                    }

                    if ($len > 4) {
                        /**
                         * 把网址按以下规则匹配
                         * /{action}/{参数名1}-{参数值1}/{参数名2}-{参数值2}/{参数名3}-{参数值3}
                         * 其中{参数名}-{参数值} 值对不限数量
                         */
                        for ($i = 4; $i < $len; $i++) {
                            $pos = strpos($uris[$i], '-');
                            if ($pos !== false) {
                                $key = substr($uris[$i], 0, $pos);
                                $val = substr($uris[$i], $pos + 1);

                                $swRequest->get[$key] = $swRequest->request[$key] = $val;
                            }
                        }
                    }
                }

                // 默认访问控制台页面
                if (!$app) {
                    $route = $request->request('route', $configSystem->home);
                    $routes = explode('.', $route);
                    if (count($routes) == 3) {
                        $app = $routes[0];
                        $controller = $routes[1];
                        $action = $routes[2];
                    } else {
                        $response->error('路由参数（' . $route . '）无法识别！');
                        Be::release();
                        return true;
                    }
                }

                $request->setRoute($app, $controller, $action);

                if ($app != 'System' || $controller != 'Installer') {
                    $my = Be::getUser();
                    if ($my->id == 0) {
                        Be::getService('System.User')->rememberMe();
                        $my = Be::getUser();
                    }

                    // 校验权限
                    $role0 = Be::getRole(0);
                    if (!$role0->hasPermission($app, $controller, $action)) {
                        // 访问的不是公共内容，且未登录，跳转到登录页面
                        if ($my->id == 0) {
                            $return = $request->get('return', base64_encode($request->getUrl()));
                            $redirectUrl = beUrl('System.User.login', ['return' => $return]);
                            $response->error('登录超时，请生新登录！', $redirectUrl);
                            Be::release();
                            return true;
                        } else {
                            if (!$my->hasPermission($app, $controller, $action)) {
                                $response->error('您没有权限操作该功能！');
                                Be::release();
                                return true;
                            }
                        }
                    }

                    if ($my->id > 0) {
                        // 已登录用户，IP锁定功能校验
                        $configUser = Be::getConfig('System.User');
                        if ($configUser->ipLock) {
                            if ($my->this_login_ip != $request->getIp()) {
                                Be::getService('System.User')->logout();
                                $redirectUrl = beUrl('System.User.login');
                                $response->error('检测到您的账号在其它地点（'.$my->this_login_ip . ' '. $my->this_login_time.'）登录！', $redirectUrl);
                                Be::release();
                                return true;
                            }
                        }
                    }
                }

                $class = 'Be\\Mf\\App\\' . $app . '\\Controller\\' . $controller;
                if (!class_exists($class)) {
                    $response->set('code', 404);
                    $response->error('控制器 ' . $app . '/' . $controller . ' 不存在！');
                } else {
                    $instance = new $class();
                    if (method_exists($instance, $action)) {
                        $instance->$action();
                    } else {
                        $response->set('code', 404);
                        $response->error('未定义的任务：' . $action);
                    }
                }

            } catch (\Throwable $t) {
                $response->exception($t);
                Be::getLogger()->emergency($t);
            }

            Be::release();
            return true;
        });

        $this->server->start();
    }


    public function stop()
    {
        $this->server->stop();

        $sessionConfig = ConfigFactory::getInstance('System.Session');
        if ($sessionConfig->driver == 'File') {
            $dir = RuntimeFactory::getInstance()->getCachePath() . '/session';
            \Be\F\Util\FileSystem\Dir::rm($dir);
        }
    }


    public function reload()
    {
        $this->server->reload();
    }

}
