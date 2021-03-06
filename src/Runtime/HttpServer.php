<?php

namespace Be\Mf\Runtime;

use Be\F\Config\ConfigFactory;
use Be\F\Db\DbFactory;
use Be\F\Gc;
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
    private $swooleHttpServer = null;

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
        if ($this->swooleHttpServer !== null) {
            return;
        }

        $state = new \Swoole\Table(1024);
        $state->column('value', \Swoole\Table::TYPE_INT, 64);
        $state->create();
        $state->set('task', ['value' => 1]);

        \Co::set(['hook_flags' => SWOOLE_HOOK_ALL]);

        $configSystem = ConfigFactory::getInstance('System.System');
        date_default_timezone_set($configSystem->timezone);

        $configServer = ConfigFactory::getInstance('System.Server');
        $this->swooleHttpServer = new \Swoole\Http\Server($configServer->host, $configServer->port);
        $this->swooleHttpServer->state = $state;

        $setting = [
            'enable_coroutine' => true,
            'task_worker_num' => 4,
            'task_enable_coroutine' => true,
        ];

        if ($configServer->reactor_num > 0) {
            $setting['reactor_num'] = $configServer->reactor_num;
        }

        if ($configServer->worker_num > 0) {
            $setting['worker_num'] = $configServer->worker_num;
        }

        if ($configServer->max_request > 0) {
            $setting['max_request'] = $configServer->max_request;
        }

        if ($configServer->max_conn > 0) {
            $setting['max_conn'] = $configServer->max_conn;
        }

        if ($configServer->task_worker_num > 0) {
            $setting['task_worker_num'] = $configServer->task_worker_num;
        }

        if ($configServer->task_max_request > 0) {
            $setting['task_max_request'] = $configServer->task_max_request;
        }

        $this->swooleHttpServer->set($setting);

        // 初始化数据库，Redis连接池
        DbFactory::init();
        RedisFactory::init();

        if ($configServer->clearCacheOnStart) {
            $dir = RuntimeFactory::getInstance()->getCachePath();
            \Be\F\Util\FileSystem\Dir::rm($dir);
        } else {
            $sessionConfig = ConfigFactory::getInstance('System.Session');
            if ($sessionConfig->driver == 'File') {
                $dir = RuntimeFactory::getInstance()->getCachePath() . '/session';
                \Be\F\Util\FileSystem\Dir::rm($dir);
            }
        }

        $this->swooleHttpServer->on('request', function ($swooleRequest, $swooleResponse) {
            /**
             * @var \Swoole\Http\Response $swooleResponse
             */
            $swooleResponse->header('Server', 'be/mf', false);
            $uri = $swooleRequest->server['request_uri'];

            $ext = strrchr($uri, '.');
            if ($ext) {
                $ext = strtolower(substr($ext, 1));
                if (isset(self::MIME[$ext])) {
                    $rootPath = Be::getRuntime()->getRootPath();
                    if (file_exists($rootPath . $uri)) {
                        $swooleResponse->header('Content-Type', self::MIME[$ext], false);
                        //缓存
                        $lastModified = gmdate('D, d M Y H:i:s', filemtime($rootPath . $uri)) . ' GMT';
                        if (isset($swooleRequest->header['if-modified-since']) && $swooleRequest->header['if-modified-since'] == $lastModified) {
                            $swooleResponse->status(304);
                            $swooleResponse->end();
                            return true;
                        }

                        $swooleResponse->header('Last-Modified', $lastModified, false);

                        //发送Expires头标，设置当前缓存的文档过期时间，GMT格式
                        $swooleResponse->header('Expires', gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT', false);

                        //发送Cache_Control头标，设置xx秒以后文档过时,可以代替Expires，如果同时出现，max-age优先。
                        $swooleResponse->header('Cache-Control', 'max-age=31536000', false);
                        $swooleResponse->header('Pragma', 'max-age=31536000', false);

                        $swooleResponse->sendfile($rootPath . $uri);
                        return true;
                    }

                    if ($uri == '/favicon.ico') {
                        $swooleResponse->end();
                        return true;
                    }
                }
            }


            $swooleRequest->request = null;
            if ($swooleRequest->get !== null) {
                if ($swooleRequest->post !== null) {
                    $swooleRequest->request = array_merge($swooleRequest->get, $swooleRequest->post);
                } else {
                    $swooleRequest->request = $swooleRequest->get;
                }
            } else {
                if ($swooleRequest->post !== null) {
                    $swooleRequest->request = $swooleRequest->post;
                }
            }

            $request = new \Be\F\Request\Driver($swooleRequest);
            $response = new \Be\F\Response\Driver($swooleResponse);

            RequestFactory::setInstance($request);
            ResponseFactory::setInstance($response);

            // 启动 session
            Be::getSession()->start();

            try {

                // 检查网站配置， 是否暂停服务
                $configSystem = ConfigFactory::getInstance('System.System');

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

                                $swooleRequest->get[$key] = $swooleRequest->request[$key] = $val;
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
                        Gc::release(\Swoole\Coroutine::getuid());
                        return true;
                    }
                }

                $request->setRoute($app, $controller, $action);

                // 校验权限
                $role0 = Be::getRole(0);
                if (!$role0->hasPermission($app, $controller, $action)) {
                    $my = Be::getUser();
                    if ($my->id == 0) {
                        Be::getService('System.User')->rememberMe();
                        $my = Be::getUser();
                    }

                    // 访问的不是公共内容，且未登录，跳转到登录页面
                    if ($my->id == 0) {
                        $return = $request->get('return', base64_encode($request->getUrl()));
                        $redirectUrl = beUrl('System.User.login', ['return' => $return]);
                        $response->error('登录超时，请重新登录！', $redirectUrl);
                        Gc::release(\Swoole\Coroutine::getuid());
                        return true;
                    } else {
                        if (!$my->hasPermission($app, $controller, $action)) {
                            $response->error('您没有权限操作该功能！');
                            Gc::release(\Swoole\Coroutine::getuid());
                            return true;
                        }

                        // 已登录用户，IP锁定功能校验
                        $configUser = Be::getConfig('System.User');
                        if ($configUser->ipLock) {
                            if ($my->this_login_ip != $request->getIp()) {
                                Be::getService('System.User')->logout();
                                $redirectUrl = beUrl('System.User.login');
                                $response->error('检测到您的账号在其它地点（' . $my->this_login_ip . ' ' . $my->this_login_time . '）登录！', $redirectUrl);
                                Gc::release(\Swoole\Coroutine::getuid());
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
                Be::getLog()->emergency($t);
            }

            Gc::release(\Swoole\Coroutine::getuid());
            return true;
        });

        // 注册任务处理方法
        $this->swooleHttpServer->on('task', [\Be\Mf\Runtime\Task::class, 'onTask']);
        $this->swooleHttpServer->on('finish', function ($swooleHttpServer, $taskId, $data) {});

        // 定时任务进程
        $process = new \Swoole\Process([\Be\Mf\Runtime\Task::class, 'process'], false, 0, true);
        $this->swooleHttpServer->addProcess($process);

        $this->swooleHttpServer->start();
    }


    public function stop()
    {
        $this->swooleHttpServer->stop();
    }

    public function reload()
    {
        $this->swooleHttpServer->reload();
    }


    public function task($data)
    {
        $this->swooleHttpServer->task($data);
    }

    public function getSwooleHttpServer()
    {
        return $this->swooleHttpServer;
    }

}
