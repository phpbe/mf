<?php

namespace Be\App\System\Service;

use App\System\Helper\DocComment;
use Be\System\Be;
use Be\System\Service;
use Be\System\Service\ServiceException;

class Menu extends Service
{

    /**
     * 获取菜单项列表
     *
     * @param int $groupId 菜单组编号
     * @return array
     */
    public function getMenus($groupId)
    {
        return Be::newTable('system_menu')
            ->where('group_id', $groupId)
            ->orderBy('ordering', 'ASC')
            ->getObjects();
    }

    /**
     * 删除菜单
     *
     * @param int $menuId 菜单编号
     * @throws \Exception
     */
    public function deleteMenu($menuId)
    {
        $db = Be::getDb();
        $db->beginTransaction();
        try {
            Be::newTable('system_menu')->where('parent_id', $menuId)->update(['parent_id' => 0]);
            Be::newTuple('system_menu')->delete($menuId);
            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    /**
     * 将某项菜单设置为首页
     *
     * @param $menuId
     * @throws \Exception
     */
    public function setHomeMenu($menuId)
    {
        $db = Be::getDb();
        $db->beginTransaction();
        try {
            Be::newTable('system_menu')->where('home', 1)->update(['home' => 0]);
            Be::newTable('system_menu')->where('id', $menuId)->update(['home' => 1]);
            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    /**
     * 获取菜单组列表
     *
     * @return array
     */
    public function getMenuGroups()
    {
        return Be::newTable('system_menu_group')->orderBy('id.asc')->getObjects();
    }

    /**
     * 获取菜单组中总数
     *
     * @return int
     */
    public function getMenuGroupSum()
    {
        return Be::newTable('system_menu_group')->count();
    }

    /**
     * 删除菜单组
     *
     * @param $groupId
     * @throws \Exception
     */
    public function deleteMenuGroup($groupId)
    {
        $db = Be::getDb();
        $db->beginTransaction();
        try {
            Be::newTable('system_menu')->where('group_id', $groupId)->delete();
            Be::newTuple('system_menu_group')->delete($groupId);
            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
    }


    /**
     * 更新菜单
     *
     * @param string $menuName 菜单名
     * @throws \Exception
     */
    public function update($menuName)
    {
        if ($menuName == 'Admin') {
            $this->updateAdminMenu();
            return;
        }

        $group = Be::newTuple('system_menu_group');
        $group->load(array('class_name' => $menuName));
        if (!$group->id) {
            throw new ServiceException('未找到调用类名为 ' . $menuName . ' 的菜单！');
        }

        $menus = Be::newTable('system_menu')
            ->where('group_id', $group->id)
            ->orderBy('ordering', 'ASC')
            ->getObjects();

        $code = '<?php' . "\n";
        $code .= 'namespace Cache\\System\\Menu;' . "\n";
        $code .= "\n";
        $code .= 'class ' . $group->class_name . ' extends \\Be\\System\\Menu' . "\n";
        $code .= '{' . "\n";
        $code .= '  public function __construct()' . "\n";
        $code .= '  {' . "\n";
        foreach ($menus as $menu) {
            if ($menu->home == 1) {
                $homeParams = array();

                $menuParams = $menu->params;
                if ($menuParams == '') $menuParams = $menu->url;

                if (strpos($menuParams, '=')) {
                    $menuParams = explode('&', $menuParams);
                    foreach ($menuParams as $menuParam) {
                        $menuParam = explode('=', $menuParam);
                        if (count($menuParam) == 2) $homeParams[$menuParam[0]] = $menuParam[1];
                    }
                }

//                $configSystem = Be::getConfig('System', 'Config');
//                if (serialize($configSystem->homeParams) != serialize($homeParams)) {
//                    $configSystem->homeParams = $homeParams;
//                    $configSystem->updateConfig('System', $configSystem);
//                }
            }

            $params = [];
            if ($menu->params) {
                parse_str($menu->params, $params);
            }
            $param = var_export($params, true);

            $url = $menu->url;
            if (substr($url, 0, 7) == 'http://' || substr($url, 0, 8) == 'https://') {
                $url = '\'' . $url . '\'';
            } else {
                $parts = explode('.', $url);
                if (count($parts) == 3) {
                    $url = 'url(\'' . $parts[0] . '\', \'' . $parts[1] . '\', \'' . $parts[2] . '\', ' . $param . ')';
                }
            }

            $code .= '    $this->addMenu(' . $menu->id . ', ' . $menu->parent_id . ', \'' . $menu->name . '\', ' . $url . ', \'' . $menu->target . '\', ' . $param . ', ' . $menu->home . ');' . "\n";
        }
        $code .= '  }' . "\n";
        $code .= '}' . "\n";

        $path = Be::getRuntime()->getCachePath() . '/System/Menu/' . $group->class_name . '.php';
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        file_put_contents($path, $code, LOCK_EX);
        chmod($path, 0755);
    }


    private $adminMenu = null;

    /**
     * 获取后台菜单
     */
    public function getAdminMenu()
    {
        if ($this->adminMenu !== null) return $this->adminMenu;

        $adminMenu = [];

        $apps = Be::getService('System', 'App')->getApps();
        foreach ($apps as $app) {

            $appName = $app->name;
            $controllerDir = Be::getRuntime()->getRootPath() . '/app/' . $appName . '/AdminController';
            if (!file_exists($controllerDir) && !is_dir($controllerDir)) continue;

                $controllers = scandir($controllerDir);
                foreach ($controllers as $controller) {
                    if ($controller == '.' || $controller == '..' || is_dir($controllerDir . '/' . $controller)) continue;

                $controller = substr($controller, 0, -4);
                $className = 'App\\' . $appName . '\\AdminController\\' . $controller;
                if (!class_exists($className)) continue;

                $reflection = new \ReflectionClass($className);

                $classMenuGroup = [];

                // 类注释
                $classComment = $reflection->getDocComment();
                $parseClassComments = DocComment::parse($classComment);
                foreach ($parseClassComments as $key => $val) {
                    if ($key == 'be-menu-group') {
                        $classMenuGroup['label'] = $val;
                    } else {
                        if (substr($key, 0, 14) == 'be-menu-group-') {
                            $classMenuGroup[substr($key, 14)] = $val;
                        }
                    }
                }

                $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
                foreach ($methods as &$method) {
                    $methodName = $method->getName();
                    $methodComment = $method->getDocComment();
                    $methodComments = DocComment::parse($methodComment);

                    $menuGroup = [];
                    $menu = [];
                    foreach ($methodComments as $key => $val) {

                        if ($key == 'be-menu-group') {
                            $menuGroup['label'] = $val;
                        } else {
                            if (substr($key, 0, 14) == 'be-menu-group-') {
                                $menuGroup[substr($key, 14)] = $val;
                            }
                        }

                        if ($key == 'be-menu') {
                            $menu['label'] = $val;
                        } else {
                            if (substr($key, 0, 8) == 'be-menu-') {
                                $menu[substr($key, 8)] = $val;
                            }
                        }
                    }

                    if (!$menuGroup) {
                        $menuGroup = $classMenuGroup;
                    }

                    if (!$menuGroup || !$menu) {
                        continue;
                    }

                    $app->key = $appName;
                    $menuGroup['key'] = $appName . '.' . $controller;
                    $menu['key'] = $appName . '.' . $controller . '.' . $methodName;
                    $menu['url'] = 'adminUrl(\''.$appName.'\', \''.$controller.'\', \''.$methodName.'\')';

                    if (!isset($adminMenu[$appName])) {
                        $adminMenu[$appName] = [
                            'app' => $app,
                            'groups' => []
                        ];
                    }

                    if (!isset($adminMenu[$appName]['groups'][$menuGroup['label']])) {
                        $adminMenu[$appName]['groups'][$menuGroup['label']] = [
                            'group' => $menuGroup,
                            'menus' => [
                                $menu
                            ]
                        ];
                    } else {
                        $adminMenu[$appName]['groups'][$menuGroup['label']]['group'] = array_merge($adminMenu[$appName]['groups'][$menuGroup['label']]['group'], $menuGroup);
                        $adminMenu[$appName]['groups'][$menuGroup['label']]['menus'][] = $menu;
                    }
                }
            }
        }

        $this->adminMenu = $adminMenu;
        return $adminMenu;
    }

    /**
     * 更新事台菜单
     */
    public function updateAdminMenu()
    {
        $adminMenu = $this->getAdminMenu();

        $code = '<?php' . "\n";
        $code .= 'namespace Cache\\System\\Menu;' . "\n";
        $code .= "\n";
        $code .= 'class Admin extends \\Be\\System\\AdminMenu' . "\n";
        $code .= '{' . "\n";
        $code .= '  public function __construct()' . "\n";
        $code .= '  {' . "\n";

        foreach ($adminMenu as $k => $v) {
            $app = $v['app'];
            $code .= '    $this->addAdminMenu(\'' . $app->key . '\', \'0\', \'' . $app->getIcon() . '\',\'' . $app->getLabel() . '\', \'\', \'\');' . "\n";
            foreach ($v['groups'] as $key => $val) {
                $group = $val['group'];
                $code .= '    $this->addAdminMenu(\'' . $group['key'] . '\',\'' . $app->key . '\',\'' . (isset($group['icon']) ? $group['icon'] : 'folder') . '\',\'' . $group['label'] . '\', \'\', \'\');' . "\n";
                foreach ($val['menus'] as $menu) {
                    $code .= '    $this->addAdminMenu(\'' . $menu['key'] . '\', \'' . $group['key'] . '\', \'' . (isset($menu['icon']) ? $menu['icon'] : 'right') . '\', \'' . $menu['label'] . '\', ' . $menu['url'] . ', \'' . (isset($menu['target']) ? $menu['target'] : '') . '\');' . "\n";
                }
            }
        }
        $code .= '  }' . "\n";
        $code .= '}' . "\n";

        $path = Be::getRuntime()->getCachePath() . '/System/Menu/Admin.php';
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        file_put_contents($path, $code, LOCK_EX);
        chmod($path, 0755);
    }

}
