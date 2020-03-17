<?php
namespace Be\App\System\Controller;

use App\System\Plugin\Curd;
use Be\System\Be;
use Be\System\Db\Tuple;
use Be\System\Event;
use Be\System\Request;
use Be\System\Response;
use Be\System\Controller;

/**
 * Class User
 * @package App\System\Controller
 *
 * @be-menu-group 用户
 * @be-menu-group-icon user
 *
 * @be-permission-group 用户
 */
class User extends Controller
{

    // 登陆页面
    public function login()
    {
        if (Request::isPost()) {
            $username = Request::json('username', '');
            $password = Request::json('password', '');
            $ip = Request::ip();
            try {
                $serviceAdminUser = Be::getService('System.User');
                $serviceAdminUser->login($username, $password, $ip);
                Response::success('登录成功！');
            } catch (\Exception $e) {
                Response::error($e->getMessage());
            }
        } else {

            $my = Be::getUser();
            if ($my->id > 0) {
                Response::redirect(url('System.System.dashboard'));
            }

            Response::setTitle('登录');
            Response::display();
        }
    }


    // 退出登陆
    public function logout()
    {
        try {
            Be::getService('System.User')->logout();
            Response::success('成功退出！', url('System.User.login'));
        } catch (\Exception $e) {
            Response::error($e->getMessage());
        }
    }

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
            'title' => '用户列表',
            'table' => 'system_user',

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
                        'keyValues' => Be::getService('System.Role')->getRoleKeyValues()
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
            $tuple->password = Be::getService('System.User')->encryptPassword($tuple->password, $salt);
            $tuple->register_time = time();
            $tuple->last_login_time = 0;
        });

        Curd::on('AfterCreate', function(Tuple $tuple) {
            // 上传头像
            $avatar = Request::files('avatar');
            if ($avatar && $avatar['error'] == 0) {
                Be::getService('System.User')->uploadAvatar($tuple, $avatar);
            }
        });

        Curd::create([
            'title' => '新增用户',
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
                $tuple->password = Be::getService('System.User')->encryptPassword($tuple->password);
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
                Be::getService('System.User')->uploadAvatar($tuple, $avatar);
            }
        });

        Curd::edit([
            'name' => '编辑用户',
            'table' => 'system_user'
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
                throw new \Exception('默认用户不能禁用');
            }

            $my = Be::getUser();
            if ($tuple->id == $my->id) {
                throw new \Exception('不能禁用自已的账号');
            }
        });

        Curd::block([
            'title' => '禁用用户',
            'table' => 'system_user',
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
            'table' => 'system_user',
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
                throw new \Exception('默认用户不能删除');
            }

            $my = Be::getUser();
            if ($tuple->id == $my->id) {
                throw new \Exception('不能删除自已');
            }
        });

        Curd::delete([
            'title' => '用户',
            'table' => 'system_user',
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
            'title' => '用户',
            'table' => 'system_user',
            'fields' => [
                // 未指定时取表的所有字段
                'items' => [

                ]
            ],
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
            Be::getService('System.User')->initAvatar($id);

            Be::getService('System.AdminLog')->addLog('删除管理员账号：#' . $id . ' 头像');

            Be::getDb()->commit();
        } catch (\Exception $e) {

            Be::getDb()->rollback();
            Response::error($e->getMessage());
        }

        Response::success('删除头像成功！');
    }


}
