<?php

namespace Be\Mf\App\System\Controller;

use Be\Mf\Plugin\Toolbar\Item\ToolbarItemButtonDropDown;
use Be\Mf\Plugin\Form\Item\FormItemSelect;
use Be\Mf\Plugin\Table\Item\TableItemAvatar;
use Be\Mf\Plugin\Table\Item\TableItemSwitch;
use Be\Mf\Be;

/**
 * Class User
 * @package App\System\Controller
 *
 * @BeMenuGroup("用户报表", icon="el-icon-fa fa-user", ordering="10")
 * @BePermissionGroup("用户报表", ordering="10")
 */
class UserReport
{

    /**
     * 用户报表
     *
     * @BeMenu("用户报表", icon="el-icon-fa fa-users", ordering="10")
     * @BePermission("用户报表", ordering="10")
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

        Be::getPlugin('Report')->setting([
            'label' => '用户报表',
            'sql' => [
                'count' => 'SELECT COUNT(*) FROM system_user a LEFT JOIN system_role b ON a.role_id = b.id WHERE 1 {where}',
                'data' => 'SELECT 
                              a.id,
                              a.avatar,
                              a.username, 
                              a.email, 
                              a.name, 
                              a.create_time, 
                              a.is_enable, 
                              b.name role_name 
                            FROM system_user a 
                            LEFT JOIN system_role b ON a.role_id = b.id 
                            WHERE 1 {where}',
                ],
            'lists' => [
                'title' => '用户报表',
                'form' => [
                    'items' => [
                        [
                            'name' => 'role_id',
                            'label' => '角色',
                            'table' => 'a',
                            'driver' => FormItemSelect::class,
                            'keyValues' => ['' => '所有角色'] + $roleKeyValues,
                        ],
                        [
                            'name' => 'username',
                            'label' => '用户名',
                            'table' => 'a',
                        ],
                        [
                            'name' => 'name',
                            'label' => '名称',
                            'table' => 'a',
                        ],
                        [
                            'name' => 'email',
                            'label' => '邮箱',
                            'table' => 'a',
                        ],
                        [
                            'name' => 'is_enable',
                            'label' => '启用状态',
                            'table' => 'a',
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
                                    return Be::getRequest()->getDataUrl() . '/System/User/Avatar/' . $row['avatar'];
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
                            'name' => 'role_name',
                            'label' => '角色',
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
                            'width' => '90',
                            'exportValue' => function ($row) {
                                return $row['is_enable'] ? '启用' : '禁用';
                            },
                        ],
                    ],
                ],
            ],

            'export' => [],

        ])->execute();
    }


}
