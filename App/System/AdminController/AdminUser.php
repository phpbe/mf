<?php

namespace Be\App\System\AdminController;

use App\System\Plugin\Curd;
use Be\System\Be;
use Be\System\Db\Tuple;
use Be\System\Event;
use Be\System\Request;
use Be\System\Response;
use Be\System\AdminController;
use Be\Util\Random;


/**
 * @be-menu-group 管理员
 * @be-menu-group-icon user
 * @be-permission-group 管理员
 */
class AdminUser extends AdminController
{

    /**
     * 管理员列表
     *
     * @be-menu 管理员列表
     * @be-menu-icon user
     *
     * @be-permission 管理员列表
     */
    public function users() {
        Curd::lists([
            'title' => '管理员',
            'table' => ['System', 'AdminUser'],

            'search' => [

                'items' => [
                    [
                        'name' => 'username',
                        'label' => '用户名',
                        'driver' => \Be\System\App\SearchItem\SearchItemString::class,
                    ],
                    [
                        'name' => 'name',
                        'label' => '名称',
                        'driver' => \Be\System\App\SearchItem\SearchItemString::class,
                    ],
                    [
                        'name' => 'email',
                        'label' => '邮箱',
                        'driver' => \Be\System\App\SearchItem\SearchItemString::class,
                    ],
                    [
                        'name' => 'block',
                        'label' => '状态',
                        'driver' => \Be\System\App\SearchItem\SearchItemInt::class,
                        'keyValues' => [
                            '' => '不限',
                            '0' => '启用',
                            '1' => '禁用',
                        ],
                        'callback' => function() {

                        }
                    ],
                    [
                        'name' => 'role_id',
                        'label' => '角色',
                        'driver' => \Be\System\App\SearchItem\SearchItemInt::class,
                        'keyValues' => Be::getService('System', 'AdminRole')->getRoleKeyValues()
                    ]
                ]

            ],

            'fields' => [


                // 未指定时取表的所有字段
                'items' => [

                ]

            ],

            'toolbar' => [

                'items' => [
                    [
                        'name' => 'create',
                        'label' => '新建',
                        'ui' => [
                            'icon' => 'plus',
                        ]
                    ],
                    [
                        'name' => 'unblock',
                        'label' => '批量启用',
                        'ui' => [
                            'icon' => 'check',
                            'type' => 'primary',
                        ]
                    ],
                    [
                        'name' => 'block',
                        'label' => '批量禁用',
                        'ui' => [
                            'icon' => 'stop',
                            'type' => 'danger',
                        ]
                    ],
                    [
                        'name' => 'delete',
                        'label' => '批量删除',
                        'ui' => [
                            'icon' => 'delete',
                            'type' => 'danger'
                        ]
                    ],
                    [
                        'name' => 'export',
                        'label' => '导出',
                        'ui' => [
                            'icon' => 'download',
                        ]
                    ],
                ]
            ],

            'operation' => [

                'label' => '操作',
                'position' => 'right', // left / right
                'align' => 'center',

                'items' => [
                    [
                        'name' => 'detail',
                        'label' => '查看',
                        'ui' => [
                            'icon' => 'search',
                        ]
                    ],
                    [
                        'name' => 'edit',
                        'label' => '编辑',
                        'ui' => [
                            'icon' => 'edit',
                        ]
                    ],
                    [
                        'name' => 'unblock',
                        'label' => '启用',
                        'ui' => [
                            'icon' => 'check',
                            'type' => 'primary',
                        ]
                    ],
                    [
                        'name' => 'block',
                        'label' => '禁用',
                        'ui' => [
                            'icon' => 'stop',
                            'type' => 'danger',
                        ]
                    ],
                    [
                        'name' => 'delete',
                        'label' => '删除',
                        'ui' => [
                            'icon' => 'delete',
                            'type' => 'danger'
                        ]
                    ],
                ]

            ],

        ]);
    }

    /**
     * 创建
     *
     * @be-menu 新增管理员
     * @be-menu-icon user-add
     *
     * @be-permission 创建
     */
    public function create() {
        Curd::on('BeforeCreate', function(Tuple $tuple) {
            $salt = Random::complex(32);
            $tuple->password = Be::getService('System', 'AdminUser')->encryptPassword($tuple->password, $salt);
            $tuple->register_time = time();
            $tuple->last_login_time = 0;
        });

        Curd::on('AfterCreate', function(Tuple $tuple) {
            // 上传头像
            $avatar = Request::files('avatar');
            if ($avatar && $avatar['error'] == 0) {
                Be::getService('System', 'AdminUser')->uploadAvatar($tuple, $avatar);
            }
        });

        Curd::create([
            'title' => '新增管理员',
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
                $tuple->password = Be::getService('System', 'AdminUser')->encryptPassword($tuple->password);
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
                Be::getService('System', 'AdminUser')->uploadAvatar($tuple, $avatar);
            }
        });

        Curd::edit([
            'title' => '编辑管理员',
            'table' => ['System', 'AdminUser'],
        ]);
    }

    /**
     * 屏蔽
     *
     * @be-permission 屏蔽
     */
    public function block() {
        Curd::on('BeforeBlock', function($tuple) {
            if ($tuple->id == 1) {
                throw new \Exception('默认管理员不能禁用');
            }

            $my = Be::getAdminUser();
            if ($tuple->id == $my->id) {
                throw new \Exception('不能禁用自已的账号');
            }
        });

        Curd::block([
            'title' => '禁用管理员',
            'table' => ['System', 'AdminUser'],
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
            'title' => '启用管理员',
            'table' => ['System', 'AdminUser'],
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
        Curd::on('BeforeDelete', function($tuple) {
            if ($tuple->id == 1) {
                throw new \Exception('默认管理员不能删除');
            }

            $my = Be::getAdminUser();
            if ($tuple->id == $my->id) {
                throw new \Exception('不能删除自已');
            }
        });

        Curd::delete([
            'title' => '删除管理员',
            'table' => ['System', 'AdminUser'],
            'field' => 'is_delete',
            'value' => 1,
        ]);
    }


    /**
     * 导出
     *
     * @be-permission 导出
     */
    public function export() {
        Curd::export([
            'title' => '管理员',
            'table' => ['System', 'AdminUser'],
            'fields' => [
                // 未指定时取表的所有字段
                'items' => [

                ]
            ],
        ]);
    }

    // 登陆页面
    public function login()
    {
        if (Request::isPost()) {
            $username = Request::json('username', '');
            $password = Request::json('password', '');
            $ip = Request::ip();
            try {
                $serviceAdminUser = Be::getService('System', 'AdminUser');
                $serviceAdminUser->login($username, $password, $ip);
                Response::success('登录成功！');
            } catch (\Exception $e) {
                Response::error($e->getMessage());
            }
        } else {

            $my = Be::getAdminUser();
            if ($my->id > 0) {
                Response::redirect(adminUrl('System', 'System', 'dashboard'));
            }

            Response::setTitle('登录');
            Response::display();
        }
    }


    // 退出登陆
    public function logout()
    {
        try {
            Be::getService('System', 'AdminUser')->logout();
            Response::success('成功退出！', adminUrl('System', 'AdminUser', 'login'));
        } catch (\Exception $e) {
            Response::error($e->getMessage());
        }
    }


    public function initAvatar()
    {
        Be::getDb()->startTransaction();
        try {

            $id = Request::get('id', 0, 'int');
            Be::getService('System', 'AdminUser')->initAvatar($id);

            Be::getService('System', 'AdminLog')->addLog('删除管理员账号：#' . $id . ' 头像');
            
            Be::getDb()->commit();
        } catch (\Exception $e) {

            Be::getDb()->rollback();
            Response::error($e->getMessage());
        }

        Response::success('删除头像成功！');
    }


}

