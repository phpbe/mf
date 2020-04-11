<?php

namespace Be\App\System\Controller;


use Be\Plugin\Lists\Field\FieldItemAvatar;
use Be\Plugin\Lists\Field\FieldItemSwitch;
use Be\Plugin\Lists\Search\SearchItemInput;
use Be\Plugin\Lists\Search\SearchItemSelect;
use Be\Plugin\Lists\Toolbar\ToolbarItemButton;
use Be\System\Be;
use Be\System\Db\Tuple;
use Be\System\Request;
use Be\System\Response;
use Be\System\Controller;
use Be\Util\Random;

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
     * 用户管理
     *
     * @be-menu 用户管理
     * @be-menu-icon user
     *
     * @be-permission 用户管理
     */
    public function users()
    {
        Be::getPlugin('Curd')->lists([
            'title' => '用户列表',
            'table' => 'system_user',
            'search' => [
                'items' => [
                    [
                        'name' => 'username',
                        'label' => '用户名',
                        'driver' => SearchItemInput::class,
                    ],
                    [
                        'name' => 'name',
                        'label' => '名称',
                        'driver' => SearchItemInput::class,
                    ],
                    [
                        'name' => 'email',
                        'label' => '邮箱',
                        'driver' => SearchItemInput::class,
                    ],
                    [
                        'name' => 'block',
                        'label' => '状态',
                        'driver' => SearchItemSelect::class,
                        'keyValues' => [
                            '' => '不限',
                            '0' => '启用',
                            '1' => '禁用',
                        ]
                    ],
                    [
                        'name' => 'role_id',
                        'label' => '角色',
                        'driver' => SearchItemSelect::class,
                        'keyValues' => Be::getService('System.Role')->getRoleKeyValues()
                    ]
                ],

                'ui' => [
                    'form' => [
                        'size' => 'small'
                    ],
                ],
            ],

            'fields' => [


                // 未指定时取表的所有字段
                'items' => [
                    [
                        'name' => 'avatar_s',
                        'label' => '头像',
                        'driver' => FieldItemAvatar::class,
                        'value' => function ($row) {
                            if ($row->avatar_s == '') {
                                return Be::getRuntime()->getRootUrl() . '/' . Be::getProperty('App.System')->path . '/Template/User/images/avatar/small.png';
                            } else {
                                return Be::getRuntime()->getDataUrl() . '/System/User/Avatar' . $row->avatar_s;
                            }
                        }
                    ],
                    [
                        'name' => 'username',
                        'label' => '用户名',
                    ],
                    [
                        'name' => 'email',
                        'label' => '邮箱',
                    ],
                    [
                        'name' => 'name',
                        'label' => '名称',
                    ],
                    [
                        'name' => 'block',
                        'label' => '启用/禁用',
                        'driver' => FieldItemSwitch::class,
                        'action' => 'toggleBlock'
                    ],
                    [
                        'name' => 'name',
                        'label' => '操作',
                        'items' => [


                        ]
                    ],
                ]

            ],

            'toolbar' => [

                'items' => [
                    [
                        'name' => 'create',
                        'label' => '新建',
                        'driver' => ToolbarItemButton::class,
                        'target' => 'pop', // 'ajax - ajax请求 / pop - 弹出新窗口 / self - 当前页面 / blank - 新页面'
                        'url' => '',    // 拽定网址
                        'ui' => [
                            'icon' => 'plus',
                        ]
                    ],
                    [
                        'name' => 'toggle',
                        'label' => '批量启用',
                        'action' => 'toggleBlock',
                        'value' => 0,
                        'ui' => [
                            'icon' => 'check',
                            'type' => 'primary',
                        ]
                    ],
                    [
                        'name' => 'toggle',
                        'label' => '批量禁用',
                        'action' => 'toggleBlock',
                        'value' => 1,
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
    public function create()
    {

        Be::getPlugin('Curd')->on('BeforeCreate', function (Tuple $tuple) {
            $salt = Random::complex(32);
            $tuple->password = Be::getService('System.User')->encryptPassword($tuple->password, $salt);
            $tuple->register_time = time();
            $tuple->last_login_time = 0;
        })->on('AfterCreate', function (Tuple $tuple) {
            // 上传头像
            $avatar = Request::files('avatar');
            if ($avatar && $avatar['error'] == 0) {
                Be::getService('System.User')->uploadAvatar($tuple, $avatar);
            }
        })->create([
            'title' => '新增用户',
            'table' => 'System.User'
        ]);
    }


    /**
     * 编辑
     *
     * @be-permission 编辑
     */
    public function edit()
    {

        Be::getPlugin('Curd')->on('BeforeEdit', function ($tuple) {
            if ($tuple->password != '') {
                $tuple->password = Be::getService('System.User')->encryptPassword($tuple->password);
            } else {
                unset($tuple->password);
                unset($tuple->register_time);
                unset($tuple->last_login_time);
            }
        })->on('AfterEdit', function ($tuple) {
            // 上传头像
            $avatar = Request::files('avatar');
            if ($avatar && $avatar['error'] == 0) {
                Be::getService('System.User')->uploadAvatar($tuple, $avatar);
            }
        })->edit([
            'name' => '编辑用户',
            'table' => 'system_user'
        ]);
    }

    /**
     * 屏蔽
     *
     * @be-permission 屏蔽
     */
    public function toggleBlock()
    {
        $plugin = Be::getPlugin('Curd');

        $value = Request::request('value', 1);
        if ($value) {
            $plugin->on('BeforeToggle', function ($tuple) {
                if ($tuple->id == 1) {
                    throw new \Exception('默认用户不能禁用');
                }

                $my = Be::getUser();
                if ($tuple->id == $my->id) {
                    throw new \Exception('不能禁用自已的账号');
                }
            });
        }

        $plugin->toggle([
            'title' => $value ? '禁用用户' : '启用用户',
            'table' => 'system_user',
            'field' => 'block',
            'value' => $value,
        ]);
    }


    /**
     * 删除
     *
     * @be-permission 删除
     */
    public function delete()
    {
        Be::getPlugin('Curd')->on('BeforeDelete', function ($tuple) {
            if ($tuple->id == 1) {
                throw new \Exception('默认用户不能删除');
            }

            $my = Be::getUser();
            if ($tuple->id == $my->id) {
                throw new \Exception('不能删除自已');
            }
        })->delete([
            'title' => '删除用户',
            'table' => 'system_user'
        ]);
    }

    /**
     * 导出
     *
     * @be-permission 导出
     */
    public function export()
    {
        Be::getPlugin('Curd')->export([
            'title' => '导出用户',
            'table' => 'system_user'
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
            Be::getDb()->commit();

            SystemLog('删除管理员账号：#' . $id . ' 头像');

        } catch (\Exception $e) {

            Be::getDb()->rollback();
            Response::error($e->getMessage());
        }

        Response::success('删除头像成功！');
    }


}
