<?php

namespace Be\App\System\Controller;


use Be\Plugin\Lists\ListItem\ListItemAvatar;
use Be\Plugin\Lists\ListItem\ListItemSwitch;
use Be\Plugin\Lists\ListItem\ListItemText;
use Be\Plugin\Lists\OperationItem\OperationItemButton;
use Be\Plugin\Lists\SearchItem\SearchItemInput;
use Be\Plugin\Lists\SearchItem\SearchItemSelect;
use Be\Plugin\Lists\ToolbarItem\ToolbarItemButton;
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
     * @be-permission 用户管理
     */
    public function users()
    {
        Be::getPlugin('Curd')->execute([

            'label' => '用户管理',
            'table' => 'system_user',

            'lists' => [
                'title' => '用户列表',
                'search' => [
                    'items' => [
                        [
                            'field' => 'username',
                            'label' => '用户名',
                            'driver' => SearchItemInput::class,
                        ],
                        [
                            'field' => 'name',
                            'label' => '名称',
                            'driver' => SearchItemInput::class,
                        ],
                        [
                            'field' => 'email',
                            'label' => '邮箱',
                            'driver' => SearchItemInput::class,
                        ],
                        [
                            'field' => 'block',
                            'label' => '状态',
                            'driver' => SearchItemSelect::class,
                            'keyValues' => [
                                '' => '不限',
                                '0' => '启用',
                                '1' => '禁用',
                            ]
                        ],
                        [
                            'field' => 'role_id',
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


                'toolbar' => [

                    'items' => [
                        [
                            'label' => '新增用户',
                            'task' => 'create',
                            'driver' => ToolbarItemButton::class,
                            'target' => 'pop', // 'ajax - ajax请求 / pop - 弹出新窗口 / self - 当前页面 / blank - 新页面'
                            'url' => '',    // 拽定网址
                            'ui' => [
                                'button' => [
                                    'icon' => 'plus',
                                ]
                            ]
                        ],
                        [
                            'label' => '批量启用',
                            'task' => 'toggle',
                            'driver' => ToolbarItemButton::class,
                            'data' => [
                                'field' => 'block',
                                'value' => 0,
                            ],
                            'ui' => [
                                'button' => [
                                    'icon' => 'check',
                                    'type' => 'primary',
                                ]
                            ]
                        ],
                        [
                            'label' => '批量禁用',
                            'task' => 'toggle',
                            'driver' => ToolbarItemButton::class,
                            'data' => [
                                'field' => 'block',
                                'value' => 1,
                            ],
                            'ui' => [
                                'button' => [
                                    'icon' => 'stop',
                                    'type' => 'danger',
                                ]
                            ]
                        ],
                        [
                            'label' => '批量删除',
                            'task' => 'delete',
                            'driver' => ToolbarItemButton::class,
                            'ui' => [
                                'button' => [
                                    'icon' => 'delete',
                                    'type' => 'danger'
                                ]
                            ]
                        ],
                        [
                            'label' => '导出',
                            'task' => 'export',
                            'driver' => ToolbarItemButton::class,
                            'ui' => [
                                'button' => [
                                    'icon' => 'download',
                                ]
                            ]
                        ],
                    ]
                ],

                'list' => [

                    // 未指定时取表的所有字段
                    'items' => [
                        [
                            'label' => '头像',
                            'driver' => ListItemAvatar::class,
                            'value' => function ($row) {
                                if ($row->avatar_s == '') {
                                    return Be::getRuntime()->getRootUrl() . '/' . Be::getProperty('App.System')->path . '/Template/User/images/avatar/small.png';
                                } else {
                                    return Be::getRuntime()->getDataUrl() . '/System/User/Avatar' . $row->avatar_s;
                                }
                            }
                        ],
                        [
                            'field' => 'username',
                            'label' => '用户名',
                            'driver' => ListItemText::class,
                        ],
                        [
                            'field' => 'email',
                            'label' => '邮箱',
                            'driver' => ListItemText::class,
                        ],
                        [
                            'field' => 'name',
                            'label' => '名称',
                            'driver' => ListItemText::class,
                        ],
                        [
                            'field' => 'block',
                            'label' => '启用/禁用',
                            'driver' => ListItemSwitch::class
                        ],
                    ],
                ],


                'operation' => [
                    'label' => '操作',
                    'position' => 'left',
                    'items' => [
                        [
                            'label' => '查看',
                            'driver' => OperationItemButton::class,
                            'task' => 'detail',
                        ],
                        [
                            'label' => '编辑',
                            'driver' => OperationItemButton::class,
                            'task' => 'edit',
                        ],
                        [
                            'label' => '删除',
                            'driver' => OperationItemButton::class,
                            'task' => 'delete',
                        ],
                    ]
                ],

            ],

            'create' => [
                'BeforeCreate' => function (Tuple $tuple) {
                    $salt = Random::complex(32);
                    $tuple->password = Be::getService('System.User')->encryptPassword($tuple->password, $salt);
                    $tuple->register_time = time();
                    $tuple->last_login_time = 0;
                },

                'AfterCreate' => function (Tuple $tuple) {
                    // 上传头像
                    $avatar = Request::files('avatar');
                    if ($avatar && $avatar['error'] == 0) {
                        Be::getService('System.User')->uploadAvatar($tuple, $avatar);
                    }
                },

                'title' => '新增用户'
            ],

            'edit' => [
                'BeforeEdit' => function ($tuple) {
                    if ($tuple->password != '') {
                        $tuple->password = Be::getService('System.User')->encryptPassword($tuple->password);
                    } else {
                        unset($tuple->password);
                        unset($tuple->register_time);
                        unset($tuple->last_login_time);
                    }
                },
                'AfterEdit' => function ($tuple) {
                    // 上传头像
                    $avatar = Request::files('avatar');
                    if ($avatar && $avatar['error'] == 0) {
                        Be::getService('System.User')->uploadAvatar($tuple, $avatar);
                    }
                },

                'title' => '编辑用户'
            ],

            'toggle' => [
                'BeforeToggle' => function ($tuple) {
                    if ($tuple->block == 1) {
                        if ($tuple->id == 1) {
                            throw new \Exception('默认用户不能禁用');
                        }

                        $my = Be::getUser();
                        if ($tuple->id == $my->id) {
                            throw new \Exception('不能禁用自已的账号');
                        }
                    }
                },
            ],

            'delete' => [
                'BeforeDelete' => function ($tuple) {
                    if ($tuple->id == 1) {
                        throw new \Exception('默认用户不能删除');
                    }

                    $my = Be::getUser();
                    if ($tuple->id == $my->id) {
                        throw new \Exception('不能删除自已');
                    }
                }
            ],

            'export' => [],

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
