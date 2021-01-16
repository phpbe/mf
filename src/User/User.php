<?php

namespace Be\Mf\User;

use Be\Mf\Be;

class User
{
    public $id = 0;
    public $username = '';
    public $name = '';
    public $role_id = 0;

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
     * 获取用户角色列表
     *
     * @return \Be\Mf\User\Role
     */
    public function getRole()
    {
        return Be::getRole($this->role_id);
    }

    /**
     * 获取用户角色名称
     *
     * @return string
     */
    public function getRoleName()
    {
        return Be::getRole($this->role_id)->name;
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
        if (Be::getRole($this->role_id)->hasPermission($app, $controller, $action)) {
            return true;
        }
        return false;
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

