<?php
namespace Be\App\System\Controller;

use Be\System\Be;
use Be\System\Request;
use Be\System\Response;

class System extends \Be\System\Controller
{

    // 登陆后首页
    public function dashboard()
    {
        $my = Be::getAdminUser();

        Response::setTitle('后台首页');

        $tupleAdminUser = Be::newTuple('system_admin_user');
        $tupleAdminUser->load($my->id);
        Response::set('adminUser', $tupleAdminUser);

        $tableAdminUser = Be::getTable('system_admin_userr');
        $userCount = $tableAdminUser->count();
        Response::set('userCount', $userCount);

        $serviceSystemAdminLog = Be::getService('System.AdminLog');
        $adminServiceApp = Be::getService('System.App');
        $adminServiceTheme = Be::getService('System.Theme');
        Response::set('recentLogs', $serviceSystemAdminLog->getLogs(array('userId' => $my->id, 'offset' => 0, 'limit' => 10)));
        Response::set('appCount', $adminServiceApp->getAppCount());
        Response::set('themeCount', $adminServiceTheme->getThemeCount());

        Response::display();
    }


    public function historyBack()
    {
        $libHistory = Be::getLib('History');
        $libHistory->back();
    }

}