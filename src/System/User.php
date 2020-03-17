<?php

namespace Be\System;


class User
{
    public $id = 0;
    public $username = '';
    public $name = '';
    public $roleIds = null;
    public $_roles = null;

    /**
     * User constructor.
     * @param null | object $user
     */
    public function __construct($user = null)
    {
        if ($user && is_object($user)) {
            $vars = get_object_vars($user);
            foreach ($vars as $key => $val) {
                $this->$key = $val;
            }
        }
    }

    /**
     * 获取用户角色ID列表
     *
     * @return array
     */
    public function getRoleIds()
    {
        if ($this->roleIds === null) {
            if ($this->id == 0) {
                $this->roleIds = [1];
            } else {
                $this->roleIds = Be::getTable('system_user')
                    ->where('user_id', $this->id)
                    ->getArray('role_id');
            }
        }

        return $this->roleIds;
    }

    /**
     * 获取用户角色列表
     *
     * @return array
     */
    public function getRoles()
    {
        if ($this->_roles === null) {
            $roles = [];
            $roleIds = $this->getRoleIds();
            foreach ($roleIds as $roleId) {
                $roles[] = Be::getRole($roleId);
            }

            $this->_roles = $roles;
        }

        return $this->_roles;
    }

    /**
     * 获取用户角色名称
     *
     * @return array
     */
    public function getRoleNames()
    {
        $roles = $this->getRoles();
        $roleNames = [];
        foreach ($roles as $role) {
            $roleNames[] = $role->name;
        }

        return $roleNames;
    }

    /**
     * 判断用户是否有权限访问某项功能
     *
     * @param string $app
     * @param string $controller
     * @param string $action
     * @return bool
     */
    public function hasPermission($app, $controller, $action)
    {
        $permission = false;
        $roles = $this->getRoles();
        foreach ($roles as $role) {
            if ($role->hasPermission($app, $controller, $action)) {
                $permission = true;
                break;
            }
        }

        return $permission;
    }

    /**
     * 是否游客（未登录）
     *
     * @return bool
     */
    public function isGuest() {
        return $this->id == 0;
    }

}

