<?php
namespace Be\App\System\Controller;

use Be\System\Be;
use Be\System\Request;
use Be\System\Response;

class Menu extends \Be\System\Controller
{


    // 菜单管理
    public function menus()
    {
        $groupId = Request::get('groupId', 0, 'int');

        $adminServiceMenu = Be::getService('System.Menu');

        $groups = $adminServiceMenu->getMenuGroups();
        if ($groupId == 0) $groupId = $groups[0]->id;

        Response::setTitle('菜单列表');
        Response::set('menus', $adminServiceMenu->getMenus($groupId));
        Response::set('groupId', $groupId);
        Response::set('groups', $groups);
        Response::display();
    }

    public function menusSave()
    {
        $groupId = Request::post('groupId', 0, 'int');

        $ids = Request::post('id', array(), 'int');
        $parentIds = Request::post('parentId', array(), 'int');
        $names = Request::post('name', array());
        $urls = Request::post('url', array(), 'html');
        $targets = Request::post('target', array());
        $params = Request::post('params', array());

        if (count($ids) > 0) {
            for ($i = 0, $n = count($ids); $i < $n; $i++) {
                $id = $ids[$i];

                if ($id == 0 && $names[$i] == '') continue;

                $tupleSystemMenu = Be::newTuple('system_menu');
                if ($id != 0) $tupleSystemMenu->load($id);
                $tupleSystemMenu->groupId = $groupId;
                $tupleSystemMenu->parentId = $parentIds[$i];
                $tupleSystemMenu->name = $names[$i];
                $tupleSystemMenu->url = $urls[$i];
                $tupleSystemMenu->target = $targets[$i];
                $tupleSystemMenu->params = $params[$i];
                $tupleSystemMenu->ordering = $i;
                $tupleSystemMenu->save();
            }
        }

        $tupleSystemMenuGroup = Be::newTuple('system_menu_group');
        $tupleSystemMenuGroup->load($groupId);

        $serviceSystemCache = Be::getService('System.Cache');
        $serviceSystemCache->updateMenu($tupleSystemMenuGroup->className);

        beSystemLog('修改菜单：' . $tupleSystemMenuGroup->name);

        Response::success('保存菜单成功！', beUrl('System.System.menus', ['groupId' => $groupId]));
    }


    public function ajaxMenuDelete()
    {
        $id = Request::post('id', 0, 'int');
        if (!$id) {
            Response::set('error', 2);
            Response::set('message', '参数(id)缺失！');
        } else {
            $tupleSystemMenu = Be::newTuple('system_menu');
            $tupleSystemMenu->load($id);

            $adminServiceMenu = Be::getService('System.Menu');
            if ($adminServiceMenu->deleteMenu($id)) {

                $tupleSystemMenuGroup = Be::newTuple('system_menu_group');
                $tupleSystemMenuGroup->load($tupleSystemMenu->groupId);

                $serviceSystemCache = Be::getService('System.Cache');
                $serviceSystemCache->updateMenu($tupleSystemMenuGroup->className);

                Response::set('error', 0);
                Response::set('message', '删除菜单成功！');

                beSystemLog('删除菜单: #' . $id . ' ' . $tupleSystemMenu->name);
            } else {
                Response::set('error', 3);
                Response::set('message', $adminServiceMenu->getError());
            }
        }
        Response::ajax();
    }

    public function menuSetLink()
    {
        $id = Request::get('id', 0, 'int');
        $url = Request::get('url', '', '');

        if ($url != '') $url = base64_decode($url);


        Response::set('url', $url);

        $adminServiceSystem = Be::getService('System.Admin');
        $apps = $adminServiceSystem->getApps();
        Response::set('apps', $apps);

        Response::display();
    }

    public function ajaxMenuSetHome()
    {
        $id = Request::get('id', 0, 'int');
        if ($id == 0) {
            Response::set('error', 1);
            Response::set('message', '参数(id)缺失！');
        } else {
            $tupleSystemMenu = Be::newTuple('system_menu');
            $tupleSystemMenu->load($id);

            $adminServiceMenu = Be::getService('System.Menu');
            if ($adminServiceMenu->setHomeMenu($id)) {

                $tupleSystemMenuGroup = Be::newTuple('system_menu_group');
                $tupleSystemMenuGroup->load($tupleSystemMenu->groupId);

                $serviceSystemCache = Be::getService('System.cache');
                $serviceSystemCache->updateMenu($tupleSystemMenuGroup->className);

                Response::set('error', 0);
                Response::set('message', '设置首页菜单成功！');

                beSystemLog('设置新首页菜单：#' . $id . ' ' . $tupleSystemMenu->name);
            } else {
                Response::set('error', 2);
                Response::set('message', $adminServiceMenu->getError());
            }
        }
        Response::ajax();
    }


    // 菜单分组管理
    public function menuGroups()
    {
        $adminServiceMenu = Be::getService('System.Menu');

        Response::setTitle('添加新菜单组');
        Response::set('groups', $adminServiceMenu->getMenuGroups());
        Response::display();
    }


    // 修改菜单组
    public function menuGroupEdit()
    {
        $id = Request::request('id', 0, 'int');

        $tupleMenuGroup = Be::newTuple('system_menu_group');
        if ($id != 0) $tupleMenuGroup->load($id);

        if ($id != 0)
            Response::setTitle('修改菜单组');
        else
            Response::setTitle('添加新菜单组');

        Response::set('menuGroup', $tupleMenuGroup);
        Response::display();
    }

    // 保存修改菜单组
    public function menuGroupEditSave()
    {
        $id = Request::post('id', 0, 'int');

        $className = Request::post('className', '');
        $tupleMenuGroup = Be::newTuple('system_menu_group');
        $tupleMenuGroup->load(array('className' => $className));
        if ($tupleMenuGroup->id > 0) {
            Response::error('已存在(' . $className . ')类名！', 0, beUrl('System.System.menuGroupEdit', ['id'=>$id]));
        }

        if ($id != 0) $tupleMenuGroup->load($id);
        $tupleMenuGroup->bind(Request::post());
        if ($tupleMenuGroup->save()) {
            beSystemLog($id == 0 ? ('添加新菜单组：' . $tupleMenuGroup->name) : ('修改菜单组：' . $tupleMenuGroup->name));
            Response::success($id == 0 ? '添加菜单组成功！' : '修改菜单组成功！', beUrl('System.System.menuGroups'));
        } else {
            Response::error($tupleMenuGroup->getError(), 1, beUrl('System.ystem.menuGroupEdit', ['id'=>$id]));
        }
    }


    // 删除菜单组
    public function menuGroupDelete()
    {
        $id = Request::post('id', 0, 'int');

        $tupleMenuGroup = Be::newTuple('system_menu_group');
        $tupleMenuGroup->load($id);

        if ($tupleMenuGroup->id == 0) {
            Response::setMessage('菜单组不存在！', 'error');
        } else {
            if (in_array($tupleMenuGroup->className, array('north', 'south', 'dashboard'))) {
                Response::setMessage('系统菜单不可删除！', 'error');
            } else {
                $adminServiceMenu = Be::getService('System.menu');
                if ($adminServiceMenu->deleteMenuGroup($tupleMenuGroup->id)) {
                    beSystemLog('成功删除菜单组！');
                    Response::setMessage('成功删除菜单组！');
                } else {
                    Response::setMessage($adminServiceMenu->getError(), 'error');
                }
            }
        }


        Response::redirect(beUrl('System.System.menuGroups'));

    }



}