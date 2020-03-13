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
        $my = Be::getUser();

        Response::setTitle('后台首页');

        $tupleUser = Be::newTuple('system_user');
        $tupleUser->load($my->id);
        Response::set('user', $tupleUser);

        $tableAdminUser = Be::getTable('system_user');
        $userCount = $tableAdminUser->count();
        Response::set('userCount', $userCount);

        $serviceSystemLog = Be::getService('System.SystemLog');
        $adminServiceApp = Be::getService('System.App');
        $adminServiceTheme = Be::getService('System.Theme');
        Response::set('recentLogs', $serviceSystemLog->getLogs(array('userId' => $my->id, 'offset' => 0, 'limit' => 10)));
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