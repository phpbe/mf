<?php
namespace Be\App\System\Controller;

use Be\System\Be;
use Be\System\Exception\RuntimeException;
use Be\System\Exception\ServiceException;
use Be\System\Request;
use Be\System\Response;

/**
 * @BeMenuGroup("扩展", icon="el-icon-fa fa-cube")
 * @BePermissionGroup("扩展")
 */
class App
{

    /**
     * @BeMenu("应用管理", icon="el-icon-fa fa-cubes")
     * @BePermission("应用管理")
     */
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
            Response::json();
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
            Response::json();
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

        Response::json();
    }


}

