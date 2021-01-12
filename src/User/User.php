<?php

namespace Be\Mf\User;


class User
{
    public $id = 0;
    public $username = '';
    public $name = '';
    public $role = null;

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
     * @return Role
     */
    public function getRole()
    {
        if ($this->role === null) {
            $this->role = Be::getRole($this->role_id);
        }

        return $this->role;
    }

    /**
     * 获取用户角色名称
     *
     * @return string
     */
    public function getRoleName()
    {
        return $this->getRole()->name;
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
        if ($this->getRole()->hasPermission($app, $controller, $action)) {
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

