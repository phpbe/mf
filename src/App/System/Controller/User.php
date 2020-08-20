<?php

namespace Be\App\System\Controller;


use Be\Plugin\Curd\FieldItem\FieldItemAvatar;
use Be\Plugin\Curd\FieldItem\FieldItemIndex;
use Be\Plugin\Curd\FieldItem\FieldItemSelection;
use Be\Plugin\Curd\FieldItem\FieldItemSwitch;
use Be\Plugin\Curd\OperationItem\OperationItemButton;
use Be\Plugin\Curd\SearchItem\SearchItemInput;
use Be\Plugin\Curd\SearchItem\SearchItemSelect;
use Be\Plugin\Curd\ToolbarItem\ToolbarItemButton;
use Be\System\Be;
use Be\System\Db\Tuple;
use Be\System\Exception\RuntimeException;
use Be\System\Request;
use Be\System\Response;
use Be\System\Controller;
use Be\Util\Random;
use Exception;

/**
 * Class User
 * @package App\System\Controller
 *
 * @BeMenuGroup("用户", icon="el-icon-fa fa-user")
 * @BePermissionGroup("用户")
 */
class User extends Controller
{

    /**
     * 登陆页面
     *
     * @throws RuntimeException
     */
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
            } catch (Exception $e) {
                Response::error($e->getMessage());
            }
        } else {

            $my = Be::getUser();
            if ($my->id > 0) {
                Response::redirect(beUrl('System.System.dashboard'));
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
            Response::success('成功退出！', beUrl('System.User.login'));
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    /**
     * 用户管理
     *
     * @BeMenu("用户管理", icon="el-icon-fa fa-users")
     * @BePermission("用户管理")
     */
    public function users()
    {
        $roleKeyValues = Be::getService('System.Role')->getRoleKeyValues();

        Be::getPlugin('Curd')->execute([

            'label' => '用户管理',
            'table' => 'system_user',

            'lists' => [
                'title' => '用户列表',

                'filter' => [
                    ['is_delete', '=', '0'],
                ],

                'search' => [
                    'items' => [
                        [
                            'name' => 'role_id',
                            'label' => '角色',
                            'driver' => SearchItemSelect::class,
                            'keyValues' => array_merge(['' => '所有角色'], $roleKeyValues)
                        ],
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
                            'name' => 'is_enable',
                            'label' => '启用状态',
                            'driver' => SearchItemSelect::class,
                            'keyValues' => [
                                '' => '不限',
                                '1' => '启用',
                                '0' => '禁用',
                            ]
                        ],
                    ],
                ],


                'toolbar' => [

                    'items' => [
                        [
                            'label' => '新增用户',
                            'driver' => ToolbarItemButton::class,
                            'task' => 'create',
                            'target' => 'drawer', // 'ajax - ajax请求 / dialog - 对话框窗口 / drawer - 抽屉 / self - 当前页面 / blank - 新页面'
                            'ui' => [
                                'button' => [
                                    'icon' => 'el-icon-fa fa-user-plus',
                                ]
                            ]
                        ],
                        [
                            'label' => '批量启用',
                            'driver' => ToolbarItemButton::class,
                            'task' => 'fieldEdit',
                            'postData' => [
                                'field' => 'is_enable',
                                'value' => '1',
                            ],
                            'target' => 'ajax',
                            'ui' => [
                                'button' => [
                                    'icon' => 'el-icon-fa fa-check',
                                    'type' => 'primary',
                                ]
                            ]
                        ],
                        [
                            'label' => '批量禁用',
                            'driver' => ToolbarItemButton::class,
                            'task' => 'fieldEdit',
                            'postData' => [
                                'field' => 'is_enable',
                                'value' => '0',
                            ],
                            'target' => 'ajax',
                            'ui' => [
                                'button' => [
                                    'icon' => 'el-icon-fa fa-lock',
                                    'type' => 'warning',
                                ]
                            ]
                        ],
                        [
                            'label' => '批量删除',
                            'driver' => ToolbarItemButton::class,
                            'task' => 'fieldEdit',
                            'target' => 'ajax',
                            'postData' => [
                                'field' => 'is_delete',
                                'value' => '1',
                            ],
                            'ui' => [
                                'button' => [
                                    'icon' => 'el-icon-delete',
                                    'type' => 'danger'
                                ]
                            ]
                        ],
                        [
                            'label' => '导出',
                            'task' => 'export',
                            'driver' => ToolbarItemButton::class,
                            'target' => 'blank',
                            'ui' => [
                                'button' => [
                                    'icon' => 'el-icon-fa fa-download',
                                ]
                            ]
                        ],
                    ]
                ],

                'field' => [

                    // 未指定时取表的所有字段
                    'items' => [
                        [
                            'driver' => FieldItemSelection::class,
                        ],
                        [
                            'label' => '行号',
                            'driver' => FieldItemIndex::class,
                        ],
                        [
                            'name' => 'avatar_s',
                            'label' => '头像',
                            'driver' => FieldItemAvatar::class,
                            'value' => function ($row) {
                                if ($row['avatar_s'] == '') {
                                    return Be::getProperty('App.System')->getUrl() . '/Template/User/images/avatar/small.png';
                                } else {
                                    return Be::getRuntime()->getDataUrl() . '/System/User/Avatar' . $row['avatar_s'];
                                }
                            },
                            'ui' => [
                                'avatar' => [
                                    ':size' => '32',
                                ]
                            ],
                            'width' => '60',
                        ],
                        [
                            'name' => 'username',
                            'label' => '用户名',
                            'width' => '120',
                        ],
                        [
                            'name' => 'role_id',
                            'label' => '角色',
                            'value' => function ($row) use ($roleKeyValues) {
                                $roleIds = Be::newTable('system_user_role')
                                    ->where('user_id', $row['id'])
                                    ->getArray('role_id');

                                $roleNames = [];
                                foreach ($roleIds as $roleId) {
                                    if (isset($roleKeyValues[$roleId])) {
                                        $roleNames[] = $roleKeyValues[$roleId];
                                    }
                                }
                                return implode(', ', $roleNames);
                            },
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
                            'name' => 'is_enable',
                            'label' => '启用/禁用',
                            'driver' => FieldItemSwitch::class,
                            'target' => 'ajax',
                            'task' => 'fieldEdit',
                            'width' => '90',
                        ],
                    ],
                    'exclude' => ['password', 'salt', 'remember_me_token']
                ],

                'operation' => [
                    'label' => '操作',
                    'width' => '280',
                    'items' => [
                        [
                            'label' => '查看',
                            'driver' => OperationItemButton::class,
                            'task' => 'detail',
                            'ui' => [
                                'button' => [
                                    'icon' => 'el-icon-search',
                                    'type' => 'success'
                                ]
                            ]
                        ],
                        [
                            'label' => '编辑',
                            'driver' => OperationItemButton::class,
                            'task' => 'edit',
                            'ui' => [
                                'button' => [
                                    'icon' => 'el-icon-edit',
                                    'type' => 'primary'
                                ]
                            ]
                        ],
                        [
                            'label' => '删除',
                            'driver' => OperationItemButton::class,
                            'task' => 'fieldEdit',
                            'target' => 'ajax',
                            'postData' => [
                                'field' => 'is_delete',
                                'value' => 1,
                            ],
                            'ui' => [
                                'button' => [
                                    'icon' => 'el-icon-delete',
                                    'type' => 'danger'
                                ]
                            ]
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

            'fieldEdit' => [
                'BeforeFieldEdit' => function ($tuple) {

//                    $avatar = Request::files('avatar');
//                    $avatar = Request::files('avatar');
//
//                    if ($tuple->password != '') {
//                        $tuple->password = Be::getService('System.User')->encryptPassword($tuple->password);
//                    } else {
//                        unset($tuple->password);
//                        unset($tuple->register_time);
//                        unset($tuple->last_login_time);
//                    }
                },
            ],

            'export' => [],

        ]);
    }

    /**
     * 初始化头像
     * @BePermission("编辑")
     */
    public function initAvatar()
    {
        Be::getDb()->startTransaction();
        try {

            $id = Request::get('id', 0, 'int');
            Be::getService('System.User')->initAvatar($id);
            Be::getDb()->commit();

            beSystemLog('删除管理员账号：#' . $id . ' 头像');

        } catch (Exception $e) {

            Be::getDb()->rollback();
            Response::error($e->getMessage());
        }

        Response::success('删除头像成功！');
    }


}
