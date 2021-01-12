<?php
namespace Be\Mf\App\System\Controller;

use Be\Mf\Be;
use Be\Mf\App\ControllerException;

class Admin
{

    public function __construct()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $app = $request->getAppName();
        $controller = $request->getControllerName();
        $action = $request->getActionName();

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
                throw new ControllerException('登录超时，请生新登录！', -1024, $redirectUrl);
            } else {
                if (!$my->hasPermission($app, $controller, $action)) {
                    throw new ControllerException('您没有权限操作该功能！', -1024);
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
                    throw new ControllerException('检测到您的账号在其它地点（'.$my->this_login_ip . ' '. $my->this_login_time.'）登录！', $redirectUrl);
                }
            }
        }

    }

}

