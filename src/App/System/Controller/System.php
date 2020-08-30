<?php
namespace Be\App\System\Controller;

use Be\System\Be;
use Be\System\Request;
use Be\System\Response;

class System extends \Be\System\Controller
{

    /**
     * 登陆后首页
     *
     * @throws \Be\System\Exception\DbException
     * @throws \Be\System\Exception\RuntimeException
     */
    public function dashboard()
    {
        $my = Be::getUser();

        Response::setTitle('后台首页');

        $tupleUser = Be::newTuple('system_user');
        $tupleUser->load($my->id);
        unset($tupleUser->password, $tupleUser->salt, $tupleUser->remember_me_token);
        Response::set('user', $tupleUser);

        $tableAdminUser = Be::getTable('system_user');
        $userCount = $tableAdminUser->count();
        Response::set('userCount', $userCount);

        $recentLogs = Be::getTable('system_log')
            ->where('user_id', $my->id)
            ->limit(10)
            ->getObjects();
        $serviceApp = Be::getService('System.App');
        $serviceTheme = Be::getService('System.Theme');

        Response::set('recentLogs', $recentLogs);
        Response::set('appCount', $serviceApp->getAppCount());
        Response::set('themeCount', $serviceTheme->getThemeCount());

        Response::display();
    }


    /**
     * @throws \Be\System\Exception\RuntimeException
     */
    public function historyBack()
    {
        $libHistory = Be::getLib('History');
        $libHistory->back();
    }

}