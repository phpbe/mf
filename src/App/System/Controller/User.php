<?php

namespace Be\App\System\Controller;

use Be\Plugin\Toolbar\Item\ToolbarItemButtonDropDown;
use Be\Plugin\Detail\Item\DetailItemAvatar;
use Be\Plugin\Detail\Item\DetailItemSwitch;
use Be\Plugin\Form\Item\FormItemAvatar;
use Be\Plugin\Form\Item\FormItemSelect;
use Be\Plugin\Form\Item\FormItemSwitch;
use Be\Plugin\Table\Item\TableItemAvatar;
use Be\Plugin\Table\Item\TableItemSelection;
use Be\Plugin\Table\Item\TableItemSwitch;
use Be\System\Be;
use Be\System\Db\Tuple;
use Be\System\Exception\PluginException;
use Be\System\Request;
use Be\System\Response;
use Be\System\Controller;
use Be\Util\Random;

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
     * @BePermission("*")
     */
    public function login()
    {
        if (Request::isPost()) {
            $username = Request::json('username', '');
            $password = Request::json('password', '');
            $ip = Request::ip();
            try {
                $serviceUser = Be::getService('System.User');
                $serviceUser->login($username, $password, $ip);
                Response::success('登录成功！');
            } catch (\Exception $e) {
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

    /**
     * 退出登陆
     *
     * @BePermission("*")
     */
    public function logout()
    {
        try {
            Be::getService('System.User')->logout();
            Response::success('成功退出！', beUrl('System.User.login'));
        } catch (\Exception $e) {
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
        $configUser = Be::getConfig('System.User');
        $roleKeyValues = Be::getService('System.Role')->getRoleKeyValues();
        $genderKeyValues = [
            '-1' => '保密',
            '0' => '女',
            '1' => '男',
        ];

        Be::getPlugin('Curd')->setting([

            'label' => '用户管理',
            'table' => 'system_user',

            'lists' => [
                'title' => '用户列表',

                'filter' => [
                    ['is_delete', '=', '0'],
                ],

                'form' => [
                    'items' => [
                        [
                            'name' => 'role_id',
                            'label' => '角色',
                            'driver' => FormItemSelect::class,
                            'keyValues' => ['' => '所有角色'] + $roleKeyValues,
                        ],
                        [
                            'name' => 'username',
                            'label' => '用户名',
                        ],
                        [
                            'name' => 'name',
                            'label' => '名称',
                        ],
                        [
                            'name' => 'email',
                            'label' => '邮箱',
                        ],
                        [
                            'name' => 'is_enable',
                            'label' => '启用状态',
                            'driver' => FormItemSelect::class,
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
                            'label' => '新建用户',
                            'task' => 'create',
                            'target' => 'drawer', // 'ajax - ajax请求 / dialog - 对话框窗口 / drawer - 抽屉 / self - 当前页面 / blank - 新页面'
                            'ui' => [
                                'button' => [
                                    'icon' => 'el-icon-fa fa-user-plus',
                                    'type' => 'success',
                                ]
                            ]
                        ],
                        [
                            'label' => '批量启用',
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
                            'driver' => ToolbarItemButtonDropDown::class,
                            'ui' => [
                                'button' => [
                                    'icon' => 'el-icon-fa fa-download',
                                ]
                            ],
                            'menus' => [
                                [
                                    'label' => 'CSV',
                                    'task' => 'export',
                                    'postData' => [
                                        'driver' => 'csv',
                                    ],
                                    'target' => 'blank',
                                    'ui' => [
                                        'icon' => 'el-icon-fa fa-file-text-o',
                                    ],
                                ],
                                [
                                    'label' => 'EXCEL',
                                    'task' => 'export',
                                    'postData' => [
                                        'driver' => 'excel',
                                    ],
                                    'target' => 'blank',
                                    'ui' => [
                                        'icon' => 'el-icon-fa fa-file-excel-o',
                                    ],
                                ],
                            ]
                        ],
                    ]
                ],

                'table' => [

                    // 未指定时取表的所有字段
                    'items' => [
                        [
                            'driver' => TableItemSelection::class,
                            'width' => '50',
                        ],
                        [
                            'name' => 'id',
                            'label' => 'ID',
                            'width' => '60',
                        ],
                        [
                            'name' => 'avatar',
                            'label' => '头像',
                            'driver' => TableItemAvatar::class,
                            'value' => function ($row) {
                                if ($row['avatar'] == '') {
                                    return Be::getProperty('App.System')->getUrl() . '/Template/User/images/avatar.png';
                                } else {
                                    return Be::getRuntime()->getDataUrl() . '/System/User/Avatar/' . $row['avatar'];
                                }
                            },
                            'ui' => [
                                'avatar' => [
                                    ':size' => '32',
                                ]
                            ],
                            'width' => '50',
                        ],
                        [
                            'name' => 'username',
                            'label' => '用户名',
                            'width' => '120',
                        ],
                        [
                            'name' => 'role_id',
                            'label' => '角色',
                            'keyValues' => $roleKeyValues,
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
                            'name' => 'create_time',
                            'label' => '创建时间',
                            'width' => '150',
                        ],
                        [
                            'name' => 'is_enable',
                            'label' => '启用/禁用',
                            'driver' => TableItemSwitch::class,
                            'target' => 'ajax',
                            'task' => 'fieldEdit',
                            'width' => '90',
                            'exportValue' => function ($row) {
                                return $row['is_enable'] ? '启用' : '禁用';
                            },
                        ],
                    ],
                    'exclude' => ['password', 'salt', 'remember_me_token']
                ],

                'operation' => [
                    'label' => '操作',
                    'width' => '120',
                    'items' => [
                        [
                            'label' => '查看',
                            'task' => 'detail',
                            'target' => 'drawer',
                            'ui' => [
                                'link' => [
                                    'type' => 'success'
                                ]
                            ]
                        ],
                        [
                            'label' => '编辑',
                            'task' => 'edit',
                            'target' => 'drawer',
                            'ui' => [
                                'link' => [
                                    'type' => 'primary'
                                ]
                            ]
                        ],
                        [
                            'label' => '删除',
                            'task' => 'fieldEdit',
                            'target' => 'ajax',
                            'postData' => [
                                'field' => 'is_delete',
                                'value' => 1,
                            ],
                            'ui' => [
                                'link' => [
                                    'type' => 'danger'
                                ]
                            ]
                        ],
                    ]
                ],

            ],

            'detail' => [
                'form' => [
                    'items' => [
                        [
                            'name' => 'id',
                            'label' => 'ID',
                        ],
                        [
                            'name' => 'avatar',
                            'label' => '头像',
                            'driver' => DetailItemAvatar::class,
                            'value' => function ($row) {
                                if ($row['avatar'] == '') {
                                    return Be::getProperty('App.System')->getUrl() . '/Template/User/images/avatar.png';
                                } else {
                                    return Be::getRuntime()->getDataUrl() . '/System/User/Avatar/' . $row['avatar'];
                                }
                            },
                        ],
                        [
                            'name' => 'username',
                            'label' => '用户名',
                        ],
                        [
                            'name' => 'role_id',
                            'label' => '角色',
                            'keyValues' => $roleKeyValues,
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
                            'name' => 'gender',
                            'label' => '性别',
                            'value' => function ($row) {
                                switch ($row['gender']) {
                                    case '-1':
                                        return '保密';
                                    case '0':
                                        return '女';
                                    case '1':
                                        return '男';
                                }
                                return '';
                            },
                        ],
                        [
                            'name' => 'phone',
                            'label' => '电话',
                        ],
                        [
                            'name' => 'mobile',
                            'label' => '手机',
                        ],
                        [
                            'name' => 'is_enable',
                            'label' => '启用/禁用',
                            'driver' => DetailItemSwitch::class,
                        ],
                        [
                            'name' => 'create_time',
                            'label' => '创建时间',
                        ],
                        [
                            'name' => 'last_login_time',
                            'label' => '最后一次登陆时间',
                        ],
                        [
                            'name' => 'last_login_ip',
                            'label' => '最后一次登录的IP',
                        ],
                    ]
                ],
            ],

            'create' => [
                'title' => '新建用户',
                'form' => [
                    'items' => [
                        [
                            'name' => 'avatar',
                            'label' => '头像',
                            'driver' => FormItemAvatar::class,
                            'path' => '/System/User/Avatar/',
                            'maxWidth' => $configUser->avatarWidth,
                            'maxHeight' => $configUser->avatarHeight,
                            'defaultValue' => Be::getProperty('App.System')->getUrl() . '/Template/User/images/avatar.png',
                        ],
                        [
                            'name' => 'username',
                            'label' => '用户名',
                            'required' => true,
                            'unique' => true,
                        ],
                        [
                            'name' => 'password',
                            'label' => '密码',
                            'required' => true,
                        ],
                        [
                            'name' => 'role_id',
                            'label' => '角色',
                            'driver' => FormItemSelect::class,
                            'keyValues' => $roleKeyValues,
                            'required' => true,
                        ],
                        [
                            'name' => 'email',
                            'label' => '邮箱',
                            'unique' => true,
                            'required' => true,
                        ],
                        [
                            'name' => 'name',
                            'label' => '名称',
                            'required' => true,
                        ],
                        [
                            'name' => 'gender',
                            'label' => '性别',
                            'driver' => FormItemSelect::class,
                            'keyValues' => $genderKeyValues,
                            'required' => true,
                        ],
                        [
                            'name' => 'phone',
                            'label' => '电话',
                        ],
                        [
                            'name' => 'mobile',
                            'label' => '手机',
                        ],
                        [
                            'name' => 'is_enable',
                            'label' => '启用/禁用',
                            'value' => 1,
                            'driver' => FormItemSwitch::class,
                        ],
                    ]
                ],
                'events' => [
                    'before' => function (Tuple &$tuple) {
                        $tuple->salt = Random::complex(32);
                        $tuple->password = Be::getService('System.User')->encryptPassword($tuple->password, $tuple->salt);
                        $tuple->create_time = date('Y-m-d H:i:s');
                    },
                ],
            ],

            'edit' => [
                'title' => '编辑用户',
                'form' => [
                    'items' => [
                        [
                            'name' => 'avatar',
                            'label' => '头像',
                            'driver' => FormItemAvatar::class,
                            'path' => '/System/User/Avatar/',
                            'maxWidth' => $configUser->avatarWidth,
                            'maxHeight' => $configUser->avatarHeight,
                            'defaultValue' => Be::getProperty('App.System')->getUrl() . '/Template/User/images/avatar.png',
                        ],
                        [
                            'name' => 'username',
                            'label' => '用户名',
                            'disabled' => true,
                            'required' => true,
                        ],
                        [
                            'name' => 'password',
                            'label' => '密码',
                            'value' => '',
                        ],
                        [
                            'name' => 'role_id',
                            'label' => '角色',
                            'driver' => FormItemSelect::class,
                            'keyValues' => $roleKeyValues,
                            'required' => true,
                        ],
                        [
                            'name' => 'email',
                            'label' => '邮箱',
                            'disabled' => true,
                            'required' => true,
                        ],
                        [
                            'name' => 'name',
                            'label' => '名称',
                            'required' => true,
                        ],
                        [
                            'name' => 'gender',
                            'label' => '性别',
                            'driver' => FormItemSelect::class,
                            'keyValues' => $genderKeyValues,
                            'required' => true,
                        ],
                        [
                            'name' => 'phone',
                            'label' => '电话',
                        ],
                        [
                            'name' => 'mobile',
                            'label' => '手机',
                        ],
                        [
                            'name' => 'is_enable',
                            'label' => '启用/禁用',
                            'driver' => FormItemSwitch::class,
                        ],
                    ]
                ],
                'events' => [
                    'before' => function (Tuple &$tuple) {
                        if ($tuple->password != '') {
                            $tuple->password = Be::getService('System.User')->encryptPassword($tuple->password);
                        } else {
                            unset($tuple->password);
                        }
                    }
                ]
            ],

            'fieldEdit' => [
                'events' => [
                    'before' => function ($tuple) {
                        $postData = Request::json();
                        $field = $postData['postData']['field'];
                        if ($field == 'is_enable') {
                            if ($tuple->is_enable == 0) {
                                if ($tuple->id == 1) {
                                    throw new PluginException('默认用户不能禁用');
                                }

                                $my = Be::getUser();
                                if ($tuple->id == $my->id) {
                                    throw new PluginException('不能禁用自已的账号');
                                }
                            }
                        } elseif ($field == 'is_delete') {
                            if ($tuple->is_delete == 1) {
                                if ($tuple->id == 1) {
                                    throw new PluginException('默认用户不能删除');
                                }

                                $my = Be::getUser();
                                if ($tuple->id == $my->id) {
                                    throw new PluginException('不能删除自已');
                                }
                            }
                        }
                    },
                ],
            ],

            'export' => [],

        ])->execute();
    }


}
