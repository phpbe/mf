<?php
namespace Be\App\System\Controller;

use Be\System\Be;
use Be\System\Request;
use Be\System\Response;

/**
 * @be-menu-group 扩展
 * @be-permission-group 扩展
 */
class App extends \Be\System\Controller
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
        $appName = Request::get('appName');
        if (!$appName) {
            Response::set('error', 1);
            Response::set('message', '参数(appName)缺失！');
            Response::ajax();
        }

        try {
            $serviceApp = Be::getService('System.App');
            $serviceApp->install($appName);

            Be::getService('System.AdminLog')->addLog('安装新应用：' . $appName);
            Response::success('应用安装成功！');
        } catch (\Exception $e) {
            Response::error($e->getMessage());
        }
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

