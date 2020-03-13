<?php
namespace Be\App\System\Controller;

use Be\System\Be;
use Be\System\Request;
use Be\System\Response;
use Be\System\Controller;

class AdminUserRole extends Controller
{


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

                    $tupleAdminUserRole = Be::newTuple('system_admin_user_role');
                    if ($id != 0) $tupleAdminUserRole->load($id);
                    $tupleAdminUserRole->name = $names[$i];
                    $tupleAdminUserRole->note = $notes[$i];
                    $tupleAdminUserRole->ordering = $i;
                    $tupleAdminUserRole->save();
                }
            }

            $serviceAdminUser = Be::getService('System.AdminUser');
            $serviceAdminUser->updateAdminUserRoles();

            adminLog('修改后台管理员组');

            Response::success('修改后台管理员组成功！', url('System.AdminUser.roles'));

        } else {
            $serviceAdminUser = Be::getService('System.AdminUser');
            $roles = $serviceAdminUser->getRoles();

            foreach ($roles as $role) {
                $role->userCount = $serviceAdminUser->getUserCount(array('roleId' => $role->id));
            }

            Response::setTitle('管理员角色');
            Response::set('roles', $roles);
            Response::set('tab', 'backend');
            Response::display();
        }
    }



    public function ajaxDeleteRole()
    {
        $roleId = Request::post('id', 0, 'int');
        if ($roleId == 0) {
            Response::set('error', 1);
            Response::set('message', '参数(roleId)缺失！');
            Response::ajax();
        }

        $tupleAdminUserRole = Be::newTuple('system_admin_user_role');
        $tupleAdminUserRole->load($roleId);
        if ($tupleAdminUserRole->id == 0) {
            Response::set('error', 2);
            Response::set('message', '不存在的分组');
            Response::ajax();
        }

        $adminServiceUser = Be::getService('System.User');
        $userCount = $adminServiceUser->getUserCount(array('roleId' => $roleId));
        if ($userCount > 0) {
            Response::set('error', 3);
            Response::set('message', '当前有' . $userCount . '个管理员属于这个分组，禁止删除！');
            Response::ajax();
        }

        $tupleAdminUserRole->delete();

        adminLog('删除后台管理员组：' . $tupleAdminUserRole->name);

        Response::set('error', 0);
        Response::set('message', '删除管理员组成功！');
        Response::ajax();
    }

    public function rolePermissions()
    {
        $roleId = Request::get('roleId', 0, 'int');
        if ($roleId == 0) Response::end('参数(roleId)缺失！');

        $tupleAdminUserRole = Be::newTuple('system_admin_user_role');
        $tupleAdminUserRole->load($roleId);
        if ($tupleAdminUserRole->id == 0) Response::end('不存在的分组！');

        $adminServiceApp = Be::getService('System.app');
        $apps = $adminServiceApp->getApps();

        Response::setTitle('管理员组(' . $tupleAdminUserRole->name . ')权限设置');
        Response::set('role', $tupleAdminUserRole);
        Response::set('apps', $apps);
        Response::display();
    }

    public function rolePermissionsSave()
    {
        $roleId = Request::post('roleId', 0, 'int');
        if ($roleId == 0) Response::end('参数(roleId)缺失！');

        $tupleAdminUserRole = Be::newTuple('system_admin_user_role');
        $tupleAdminUserRole->load($roleId);
        if ($tupleAdminUserRole->id == 0) Response::end('不存在的分组！');
        $tupleAdminUserRole->permission = Request::post('permission', 0, 'int');

        if ($tupleAdminUserRole->permission == -1) {
            $publicPermissions = [];
            $adminServiceApp = Be::getService('System.app');
            $apps = $adminServiceApp->getApps();
            foreach ($apps as $app) {
                $appPermissions = $app->getAdminPermissions();
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
            $tupleAdminUserRole->permissions = $permissions;
        } else {
            $tupleAdminUserRole->permissions = '';
        }

        $tupleAdminUserRole->save();

        $serviceAdminUser = Be::getService('System.AdminUser');
        $serviceAdminUser->updateAdminUserRole($roleId);

        adminLog('修改管理员组(' . $tupleAdminUserRole->name . ')权限');

        Response::success('修改管理员组权限成功！', url('System.AdminUser.roles'));
    }


}

