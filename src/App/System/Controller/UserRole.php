<?php
namespace Be\App\System\Controller;

use Be\System\Be;
use Be\System\Request;
use Be\System\Response;
use Be\System\Controller;

/**
 * Class UserRole
 * @package App\System\Controller
 * @BeMenuGroup("用户")
 * @BePermissionGroup("用户角色")
 */
class UserRole extends Controller
{

    /**
     * @BeMenu("角色管理", icon="el-icon-fa fa-user-secret")
     * @BePermission("角色列表")
     */
    public function roles()
    {


    }


}
