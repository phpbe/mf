<?php

namespace Be\Mf\App\System\Controller;

use Be\F\Db\Tuple;
use Be\F\Util\Random;
use Be\Mf\Plugin\Table\Item\TableItemLink;
use Be\Mf\Plugin\Toolbar\Item\ToolbarItemButtonDropDown;
use Be\Mf\Plugin\Detail\Item\DetailItemAvatar;
use Be\Mf\Plugin\Detail\Item\DetailItemSwitch;
use Be\Mf\Plugin\Form\Item\FormItemAvatar;
use Be\Mf\Plugin\Form\Item\FormItemSelect;
use Be\Mf\Plugin\Form\Item\FormItemSwitch;
use Be\Mf\Plugin\Table\Item\TableItemAvatar;
use Be\Mf\Plugin\Table\Item\TableItemSelection;
use Be\Mf\Plugin\Table\Item\TableItemSwitch;
use Be\Mf\Plugin\PluginException;
use Be\Mf\Be;

/**
 * Class User
 * @package App\System\Controller
 *
 * @BeMenuGroup("用户", icon="el-icon-fa fa-user", ordering="1")
 * @BePermissionGroup("用户", ordering="1")
 */
class User
{
    /**
     * 登陆页面
     *
     * @BePermission("*")
     */
    public function login()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        if ($request->isPost()) {
            $username = $request->json('username', '');
            $password = $request->json('password', '');
            $ip = $request->getIp();
            try {
                $serviceUser = Be::getService('System.User');
                $serviceUser->login($username, $password, $ip);
                $response->success('登录成功！');
            } catch (\Exception $e) {
                $response->error($e->getMessage());
            }
        } else {
            $my = Be::getUser();
            if ($my->id > 0) {
                $response->redirect(beUrl('System.System.dashboard'));
                return;
            }

            $response->set('title', '登录');
            $response->display();
        }
    }

    /**
     * 退出登陆
     *
     * @BePermission("*")
     */
    public function logout()
    {
        $response = Be::getResponse();
        try {
            Be::getService('System.User')->logout();
            $response->success('成功退出！', beUrl('System.User.login'));
        } catch (\Exception $e) {
            $response->error($e->getMessage());
        }
    }

    /**
     * 用户管理
     *
     * @BeMenu("用户管理", icon="el-icon-fa fa-users", ordering="1.1")
     * @BePermission("用户管理", ordering="1.1")
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
                            'keyValues' => $roleKeyValues,
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
                                'icon' => 'el-icon-fa fa-user-plus',
                                'type' => 'primary',
                            ]
                        ],
                        [
                            'label' => '启用',
                            'task' => 'fieldEdit',
                            'postData' => [
                                'field' => 'is_enable',
                                'value' => '1',
                            ],
                            'target' => 'ajax',
                            'ui' => [
                                'icon' => 'el-icon-fa fa-check',
                                'type' => 'primary',
                            ]
                        ],
                        [
                            'label' => '禁用',
                            'task' => 'fieldEdit',
                            'postData' => [
                                'field' => 'is_enable',
                                'value' => '0',
                            ],
                            'target' => 'ajax',
                            'ui' => [
                                'icon' => 'el-icon-fa fa-lock',
                                'type' => 'warning',
                            ]
                        ],
                        [
                            'label' => '删除',
                            'task' => 'fieldEdit',
                            'target' => 'ajax',
                            'postData' => [
                                'field' => 'is_delete',
                                'value' => '1',
                            ],
                            'ui' => [
                                'icon' => 'el-icon-delete',
                                'type' => 'danger'
                            ]
                        ],
                        [
                            'label' => '导出',
                            'driver' => ToolbarItemButtonDropDown::class,
                            'ui' => [
                                'icon' => 'el-icon-fa fa-download',
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
                                    return Be::getRequest()->getUploadUrl() . '/System/User/Avatar/' . $row['avatar'];
                                }
                            },
                            'ui' => [
                                ':size' => '32',
                            ],
                            'width' => '50',
                        ],
                        [
                            'name' => 'username',
                            'label' => '用户名',
                            'driver' => TableItemLink::class,
                            'task' => 'detail',
                            'target' => 'drawer',
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
                    'exclude' => ['password', 'salt']
                ],

                'operation' => [
                    'label' => '操作',
                    'width' => '120',
                    'items' => [
                        [
                            'label' => '编辑',
                            'task' => 'edit',
                            'target' => 'drawer',
                            'ui' => [
                                'type' => 'primary'
                            ]
                        ],
                        [
                            'label' => '删除',
                            'task' => 'fieldEdit',
                            'confirm' => '确认要删除么？',
                            'target' => 'ajax',
                            'postData' => [
                                'field' => 'is_delete',
                                'value' => 1,
                            ],
                            'ui' => [
                                'type' => 'danger'
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
                                    return Be::getRequest()->getUploadUrl() . '/System/User/Avatar/' . $row['avatar'];
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
                            'name' => 'update_time',
                            'label' => '更新时间',
                        ],
                        [
                            'name' => 'last_login_time',
                            'label' => '上次登陆时间',
                        ],
                        [
                            'name' => 'last_login_ip',
                            'label' => '上次登录的IP',
                        ],
                        [
                            'name' => 'this_login_time',
                            'label' => '本次登陆时间',
                        ],
                        [
                            'name' => 'this_login_ip',
                            'label' => '本次登录的IP',
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
                        $tuple->update_time = date('Y-m-d H:i:s');
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
                            $tuple->salt = Random::complex(32);
                            $tuple->password = Be::getService('System.User')->encryptPassword($tuple->password, $tuple->salt);
                        } else {
                            unset($tuple->password);
                        }
                        $tuple->update_time = date('Y-m-d H:i:s');
                    }
                ]
            ],

            'fieldEdit' => [
                'events' => [
                    'before' => function ($tuple) {
                        $request = Be::getRequest();
                        $postData = $request->json();
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

                        $tuple->update_time = date('Y-m-d H:i:s');
                    },
                ],
            ],

            'export' => [],

        ])->execute();
    }


}
