<?php

namespace Be\App\System\Controller;

use Be\Plugin\Form\Item\FormItemDatePickerRange;
use Be\Plugin\Form\Item\FormItemSelect;
use Be\Plugin\Table\Item\TableItemCustom;
use Be\System\Be;
use Be\System\Response;


/**
 * @BeMenuGroup("用户")
 * @BePermissionGroup("用户")
 */
class UserLoginLog
{

    /**
     * 系统日志
     *
     * @BeMenu("用户登录日志", icon="el-icon-fa fa-user-circle", ordering="10.2")
     * @BePermission("查看用户登录日志", ordering="10.2")
     */
    public function logs()
    {
        Be::getPlugin('Curd')->setting([
            'label' => '用户登录日志',
            'table' => 'system_user_login_log',
            'lists' => [
                'title' => '用户登录日志',
                'orderBy' => 'create_time',
                'orderByDir' => 'DESC',
                'form' => [
                    'items' => [
                        [
                            'name' => 'username',
                            'label' => '用户名',
                        ],
                        [
                            'name' => 'success',
                            'label' => '登录结果',
                            'driver' => FormItemSelect::class,
                            'keyValues' => [
                                '' => '不限',
                                '0' => '失败',
                                '1' => '成功',
                            ],
                        ],
                        [
                            'name' => 'description',
                            'label' => '描述',
                        ],
                        [
                            'name' => 'create_time',
                            'label' => '创建时间',
                            'driver' => FormItemDatePickerRange::class,
                        ],
                    ],
                ],

                'toolbar' => [
                    'items' => [
                        [
                            'label' => '删除三个月前系统日志',
                            'url' => beUrl('System.UserLoginLog.deleteLogs'),
                            'confirm' => '确认要删除么？',
                            "target" => 'ajax',
                            'ui' => [
                                'button' => [
                                    'icon' => 'el-icon-delete',
                                    'type' => 'danger'
                                ]
                            ],
                        ],
                        [
                            'label' => '导出',
                            'task' => 'export',
                            'target' => 'blank',
                            'ui' => [
                                'button' => [
                                    'icon' => 'el-icon-fa fa-download',
                                ]
                            ]
                        ],
                    ]
                ],

                'table' => [

                    'items' => [
                        [
                            'name' => 'id',
                            'label' => 'ID',
                            'width' => '80',
                        ],
                        [
                            'name' => 'username',
                            'label' => '用户名',
                            'width' => '120',
                        ],
                        [
                            'name' => 'success',
                            'label' => '登录结果',
                            'driver' => TableItemCustom::class,
                            'width' => '150',
                            'keyValues' => [
                                '0' => '<span class="el-tag el-tag--info el-tag--light">失败</span>',
                                '1' => '<span class="el-tag el-tag--success el-tag--light">成功</span>',
                            ],
                            'exportValue' => function($row){
                                return $row['success'] ? '成功' : '失败';
                            },
                        ],
                        [
                            'name' => 'description',
                            'label' => '描述',
                            'align' => 'left',
                        ],
                        [
                            'name' => 'create_time',
                            'label' => '创建时间',
                            'width' => '150',
                        ],
                        [
                            'name' => 'ip',
                            'label' => 'IP地址',
                            'width' => '160',
                        ],
                    ],
                ],
            ],
        ])->execute();
    }

    /**
     * 删除用户登录日志
     *
     * @BePermission("删除用户登录日志", ordering="10.21")
     */
    public function deleteLogs()
    {
        $db = Be::getDb();
        $db->startTransaction();
        try {
            Be::newTable('system_user_login_log')
                ->where('create_time', '<', date('Y-m-d H:i:s', time() - 90 * 86400))
                ->delete();
            beOpLog('删除三个月前用户登录日志！');
            $db->commit();
            Response::success('删除三个月前用户登录日志成功！');
        } catch (\Exception $e) {
            $db->rollback();
            Response::error($e->getMessage());
        }
    }

}

