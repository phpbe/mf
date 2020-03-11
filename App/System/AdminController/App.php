<?php
namespace Be\App\System\AdminController;

use Be\System\Be;
use Be\System\Request;
use Be\System\Response;

/**
 * @be-menu-group 扩展
 * @be-permission-group 扩展
 */
class App extends \Be\System\AdminController
{

    // 应用管理
    public function apps()
    {
        $adminServiceApp = Be::getService('System.App');
        $apps = $adminServiceApp->getApps();

        Response::setTitle('已安装的应用');
        Response::set('apps', $apps);
        Response::display();
    }

    public function ajaxInstallApp()
    {
        $appId = Request::get('appId', 0, 'int');
        if ($appId == 0) {
            Response::set('error', 1);
            Response::set('message', '参数(appId)缺失！');
            Response::ajax();
        }

        $adminServiceSystem = Be::getService('System.Admin');
        $remoteApp = $adminServiceSystem->getRemoteApp($appId);
        if ($remoteApp->status != '0') {
            Response::set('error', 2);
            Response::set('message', $remoteApp->description);
            Response::ajax();
        }

        $app = $remoteApp->app;
        if (file_exists(PATH_ADMIN . '/Apps/' .  $app->name . 'php')) {
            Response::set('error', 3);
            Response::set('message', '已存在安装标识为' . $app->name . '的应用');
            Response::ajax();
        }

        if ($adminServiceSystem->installApp($app)) {
            Be::getService('System.AdminLog')->addLog('安装新应用：' . $app->name);

            Response::set('error', 0);
            Response::set('message', '应用安装成功！');
        } else {
            Response::set('error', 4);
            Response::set('message', $adminServiceSystem->getError());
        }

        Response::ajax();
    }

    public function ajaxUninstallApp()
    {
        $appName = Request::get('appName', '');
        if ($appName == '') {
            Response::set('error', 1);
            Response::set('message', '参数(appName)缺失！');
            Response::ajax();
        }

        $adminServiceSystem = Be::getService('System.Admin');
        if ($adminServiceSystem->uninstallApp($appName)) {
            Be::getService('System.AdminLog')->addLog('卸载应用：' . $appName);

            Response::set('error', 0);
            Response::set('message', '应用卸载成功！');
        } else {
            Response::set('error', 2);
            Response::set('message', $adminServiceSystem->getError());
        }

        Response::ajax();
    }


}

