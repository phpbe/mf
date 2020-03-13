<?php
namespace Be\App\System\Controller;

use Be\System\Be;
use Be\System\Request;
use Be\System\Response;
use Be\System\Controller;

/**
 * Class UserRole
 * @package App\System\Controller
 * @be-menu-group 用户
 * @be-menu-group-icon user
 * @be-permission-group 用户角色
 */
class UserRole extends Controller
{

    /**
     * @be-menu 角色管理
     * @be-permission 角色列表
     */
    public function roles()
    {
        if (Request::isPost()) {

            $ids = Request::post('id', array(), 'int');
            $names = Request::post('name', array());
            $notes = Request::post('note', array());

            if (count($ids) > 0) {
                for ($i = 0, $n = count($ids); $i < $n; $i++) {
                    $id = $ids[$i];

                    if ($id == 1) continue;

                    if ($id == 0 && $names[$i] == '') continue;

                    $tupleUserRole = Be::newTuple('system_user_role');
                    if ($id != 0) $tupleUserRole->load($id);
                    $tupleUserRole->name = $names[$i];
                    $tupleUserRole->note = $notes[$i];
                    $tupleUserRole->ordering = $i;
                    $tupleUserRole->save();
                }
            }

            $serviceUser = Be::getService('System.User');
            $serviceUser->updateUserRoles();

            systemLog('修改用户角色');

            Response::success('修改后台用户组成功！', url('System.User.roles'));

        } else {
            $serviceUser = Be::getService('System.User');
            $roles = $serviceUser->getRoles();

            foreach ($roles as $role) {
                $role->userCount = $serviceUser->getUserCount(array('roleId' => $role->id));
            }

            Response::setTitle('用户角色');
            Response::set('roles', $roles);
            Response::display();
        }
    }

    /**
     * @be-permission 修改角色
     */
    public function ajaxDeleteRole()
    {
        $roleId = Request::post('id', 0, 'int');
        if ($roleId == 0) {
            Response::set('error', 1);
            Response::set('message', '参数(roleId)缺失！');
            Response::ajax();
        }

        $tupleUserRole = Be::newTuple('system_user_role');
        $tupleUserRole->load($roleId);
        if ($tupleUserRole->id == 0) {
            Response::set('error', 2);
            Response::set('message', '不存在的用户角色');
            Response::ajax();
        }

        $serviceUser = Be::getService('System.User');
        $userCount = $serviceUser->getUserCount(array('roleId' => $roleId));
        if ($userCount > 0) {
            Response::set('error', 3);
            Response::set('message', '当前有' . $userCount . '个用户属于这个分组，禁止删除！');
            Response::ajax();
        }

        $tupleUserRole->delete();

        systemLog('删除用户角色：' . $tupleUserRole->name);

        Response::set('error', 0);
        Response::set('message', '删除用户角色成功！');
        Response::ajax();
    }


    /**
     * @be-permission 角色权限配置
     */
    public function rolePermissions()
    {
        $roleId = Request::get('roleId', 0, 'int');
        if ($roleId == 0) Response::end('参数(roleId)缺失！');

        $tupleUserRole = Be::newTuple('system_user_role');
        $tupleUserRole->load($roleId);
        if ($tupleUserRole->id == 0) Response::end('不存在的分组！');

        $serviceApp = Be::getService('System.app');
        $apps = $serviceApp->getApps();

        Response::setTitle('用户角色(' . $tupleUserRole->name . ')权限设置');
        Response::set('role', $tupleUserRole);
        Response::set('apps', $apps);
        Response::display();
    }

    /**
     * @be-permission 角色权限配置
     */
    public function rolePermissionsSave()
    {
        $roleId = Request::post('roleId', 0, 'int');
        if ($roleId == 0) Response::end('参数(roleId)缺失！');

        $tupleUserRole = Be::newTuple('system_user_role');
        $tupleUserRole->load($roleId);
        if ($tupleUserRole->id == 0) Response::end('不存在的分组！');
        $tupleUserRole->permission = Request::post('permission', 0, 'int');

        if ($tupleUserRole->permission == -1) {
            $publicPermissions = [];
            $adminServiceApp = Be::getService('System.app');
            $apps = $adminServiceApp->getApps();
            foreach ($apps as $app) {
                $appPermissions = $app->getPermissions();
                if (count($appPermissions) > 0) {
                    foreach ($appPermissions as $key => $val) {
                        if ($key == '-') {
                            $publicPermissions = array_merge($publicPermissions, $val);
                        }
                    }
                }
            }

            $permissions = Request::post('permissions', array());
            $permissions = array_merge($publicPermissions, $permissions);
            $permissions = implode(',', $permissions);
            $tupleUserRole->permissions = $permissions;
        } else {
            $tupleUserRole->permissions = '';
        }

        $tupleUserRole->save();

        $serviceUserRole = Be::getService('System.UserRole');
        $serviceUserRole->updateUserRole($roleId);

        systemLog('修改用户角色(' . $tupleUserRole->name . ')权限');

        Response::success('修改用户角色权限成功！', url('System.User.roles'));
    }

}
