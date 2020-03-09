<?php
namespace Be\System;

/**
 * 菜单基类
 */
abstract class AdminMenu extends Menu
{

	/**
	 * 添加菜单项
	 *
	 * @param int $menuId 菜单编号
	 * @param int $parentId 父级菜单编号， 等于0时为顶级菜单
     * @param string $icon 图标
     * @param string $label 中文名称
	 * @param string $url 网址
	 * @param string $target 打开方式
	 */
	public function addAdminMenu($menuId, $parentId, $icon, $label, $url, $target='_self')
	{
		$menu = new \stdClass();
		$menu->id = $menuId;
		$menu->parentId = $parentId;
        $menu->icon = $icon;
		$menu->label = $label;
        $menu->url = $url;
        $menu->target = $target;

		$this->menus[$menuId] = $menu;
	}

}