<?php
namespace Be\Mf\App\System\Controller;

use Be\Mf\Be;
use Be\Framework\Response;

/**
 * Class System
 * @package Be\App\System\Controller
 * @BePermissionGroup("*")
 */
class System
{

    /**
     * 登陆后首页
     *
     * @throws \Be\System\Exception\DbException
     * @throws \Be\System\Exception\RuntimeException
     */
    public function dashboard()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $my = Be::getUser();
        if ($my->id == 0) {
            $response->redirect(beUrl('System.User.login'));
            return;
        }

        $response->set('title', '后台首页');

        $tupleUser = Be::newTuple('system_user');
        $tupleUser->load($my->id);
        unset($tupleUser->password, $tupleUser->salt, $tupleUser->remember_me_token);
        $response->set('user', $tupleUser);

        $tableAdminUser = Be::getTable('system_user');
        $userCount = $tableAdminUser->count();
        $response->set('userCount', $userCount);

        $recentLogs = Be::getTable('system_op_log')
            ->where('user_id', $my->id)
            ->orderBy('id', 'DESC')
            ->limit(5)
            ->getObjects();
        $response->set('recentLogs', $recentLogs);

        $recentLoginLogs = Be::getTable('system_user_login_log')
            ->where('username', $my->username)
            ->orderBy('id', 'DESC')
            ->limit(5)
            ->getObjects();
        $response->set('recentLoginLogs', $recentLoginLogs);

        $serviceApp = Be::getService('System.App');
        $response->set('appCount', $serviceApp->getAppCount());

        $serviceTheme = Be::getService('System.Theme');
        $response->set('themeCount', $serviceTheme->getThemeCount());

        $response->display();
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