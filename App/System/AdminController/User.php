<?php
namespace App\System\AdminController;

use App\System\Plugin\Curd;
use Be\System\Be;
use Be\System\Db\Tuple;
use Be\System\Event;
use Be\System\Request;
use Be\System\Response;
use Be\System\AdminController;

/**
 * Class User
 * @package App\System\AdminController
 *
 * @be-menu-group 用户
 * @be-menu-group-icon user
 *
 * @be-permission-group 用户
 */
class User extends AdminController
{

    /**
     * 用户列表
     *
     * @be-menu 用户列表
     * @be-menu-icon user
     *
     * @be-permission 用户列表
     */
    public function users() {
        Curd::lists([
            'name' => '用户',
            'table' => 'System.User',
            'search' => [
                'username' => [
                    'name' => '用户名',
                    'driver' => \Be\System\App\SearchItem\SearchItemString::class,
                    'uiType' => 'text'
                ],

                'name' => [
                    'name' => '名称',
                    'driver' => \Be\System\App\SearchItem\SearchItemString::class,
                    'uiType' => 'text'
                ],

                'email' => [
                    'name' => '邮箱',
                    'driver' => \Be\System\App\SearchItem\SearchItemString::class,
                    'uiType' => 'text'
                ],

                'block' => [
                    'name' => '状态',
                    'driver' => \Be\System\App\SearchItem\SearchItemInt::class,
                    'uiType' => 'select',
                    'keyValues' => ':不限|0:启用|1:禁用'
                ],

                'role_id' => [
                    'name' => '角色',
                    'driver' => \Be\System\App\SearchItem\SearchItemInt::class,
                    'uiType' => 'select',
                    'keyValues' => Be::getService('System', 'User')->getRoles()
                ]
            ],

            'toolbar' => [
                [
                    'name' => '新建',
                    'action' => 'create',
                    'icon' => 'fa fa-plus-circle',
                ],
                [
                    'name' => '批量启用',
                    'action' => 'unblock',
                    'icon' => 'fa fa-check-circle',
                    'class' => 'text-success',
                ],
                [
                    'name' => '批量禁用',
                    'action' => 'block',
                    'icon' => 'fa fa-close-circle',
                    'class' => 'text-warning',
                ],
                [
                    'name' => '批量删除',
                    'action' => 'delete',
                    'icon' => 'fa fa-times-circle',
                    'class' => 'text-danger'
                ],
                [
                    'name' => '导出',
                    'action' => 'export',
                    'icon' => 'fa fa-array-circle-down',
                ],
            ],

            'operation' => [
                [
                    'name' => '查看',
                    'action' => 'detail',
                    'icon' => 'fa fa-search',
                ],
                [
                    'name' => '编辑',
                    'action' => 'edit',
                    'icon' => 'fa fa-edit',
                ],
                [
                    'name' => '启用',
                    'action' => 'unblock',
                    'icon' => 'fa fa-close',
                    'class' => 'text-warning',
                ],
                [
                    'name' => '禁用',
                    'action' => 'block',
                    'icon' => 'fa fa-check',
                    'class' => 'text-success',
                ],
                [
                    'name' => '删除',
                    'action' => 'delete',
                    'icon' => 'fa fa-remove',
                    'class' => 'text-danger'
                ],
            ],
        ]);
    }

    /**
     * 创建
     *
     * @be-menu 新建用户
     * @be-menu-icon user-add
     *
     * @be-permission 新建
     */
    public function create() {
        Curd::on('BeforeCreate', function($tuple) {
            $tuple->password = Be::getService('System', 'User')->encryptPassword($tuple->password);
            $tuple->register_time = time();
            $tuple->last_login_time = 0;
        });

        Curd::on('AfterCreate', function($tuple) {
            // 上传头像
            $avatar = Request::files('avatar');
            if ($avatar && $avatar['error'] == 0) {
                Be::getService('System', 'User')->uploadAvatar($tuple, $avatar);
            }

            // 组户户发送一封邮件
            $configSystem = Be::getConfig('System', 'System');
            $configUser = Be::getConfig('System', 'User');

            $data = array(
                'siteName' => $configSystem->siteName,
                'username' => $tuple->username,
                'email' => $tuple->email,
                'password' => Request::post('password', ''),
                'name' => $tuple->name,
                'siteUrl' => url()
            );

            $libMail = Be::getLib('mail');

            $subject = $libMail->format($configUser->adminCreateAccountMailSubject, $data);
            $body = $libMail->format($configUser->adminCreateAccountMailBody, $data);

            $libMail = Be::getLib('mail');
            $libMail->subject($subject);
            $libMail->body($body);
            $libMail->to($tuple->email);
            $libMail->send();
        });

        Curd::create([
            'name' => '用户',
            'table' => 'System.User'
        ]);
    }

    /**
     * 编辑
     *
     * @be-permission 编辑
     */
    public function edit() {
        Curd::on('BeforeEdit', function($tuple) {
            if ($tuple->password != '') {
                $tuple->password = Be::getService('System', 'User')->encryptPassword($tuple->password);
            } else {
                unset($tuple->password);
                unset($tuple->register_time);
                unset($tuple->last_login_time);
            }
        });

        Curd::on('AfterEdit', function($tuple) {
            // 上传头像
            $avatar = Request::files('avatar');
            if ($avatar && $avatar['error'] == 0) {
                Be::getService('System', 'User')->uploadAvatar($tuple, $avatar);
            }
        });

        Curd::edit([
            'name' => '用户',
            'table' => 'System.User'
        ]);
    }

    /**
     * 屏蔽
     *
     * @be-permission 屏蔽
     */
    public function block() {
        Curd::block([
            'name' => '用户',
            'table' => 'System.User',
            'field' => 'block',
            'value' => 1,
        ]);
    }

    /**
     * 公开
     *
     * @be-permission 公开
     */
    public function unblock() {
        Curd::unblock([
            'name' => '用户',
            'table' => 'System.User',
            'field' => 'block',
            'value' => 0,
        ]);
    }


    /**
     * 删除
     *
     * @be-permission 删除
     */
    public function delete() {
        Curd::delete([
            'name' => '用户',
            'table' => 'System.User',
            'field' => 'is_delete',
            'value' => 1,
        ]);
    }

    /**
     * 初始化头像
     *
     * @be-permission 编辑
     */
    public function initAvatar()
    {
        Be::getDb()->startTransaction();
        try {

            $id = Request::get('id', 0, 'int');
            Be::getService('System', 'User')->initAvatar($id);

            Be::getService('System', 'AdminLog')->addLog('删除管理员账号：#' . $id . ' 头像');

            Be::getDb()->commit();
        } catch (\Exception $e) {

            Be::getDb()->rollback();
            Response::error($e->getMessage());
        }

        Response::success('删除头像成功！');
    }


}
