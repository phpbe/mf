<?php
namespace Be\App\System\Controller;

use Be\System\Be;
use Be\System\Response;

/**
 * Class System
 * @package Be\App\System\Controller
 * @BePermissionGroup("*")
 */
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
        if ($my->id == 0) {
            Response::redirect(beUrl('System.User.login'));
        }

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
            ->orderBy('id', 'DESC')
            ->limit(5)
            ->getObjects();
        Response::set('recentLogs', $recentLogs);

        $recentLoginLogs = Be::getTable('system_user_login_log')
            ->where('username', $my->username)
            ->orderBy('id', 'DESC')
            ->limit(5)
            ->getObjects();
        Response::set('recentLoginLogs', $recentLoginLogs);

        $serviceApp = Be::getService('System.App');
        Response::set('appCount', $serviceApp->getAppCount());

        $serviceTheme = Be::getService('System.Theme');
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