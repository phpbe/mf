<?php

namespace Be\App\System\Controller;

use Be\Plugin\Form\Item\FormItemDatePickerRange;
use Be\Plugin\Form\Item\FormItemSelect;
use Be\System\Be;
use Be\System\Response;

/**
 * @BeMenuGroup("日志")
 * @BePermissionGroup("日志")
 */
class OpLog
{

    /**
     * 操作日志
     *
     * @BeMenu("操作日志", icon="el-icon-fa fa-video-camera", ordering="10.3")
     * @BePermission("查看操作日志", ordering="10.3")
     */
    public function logs()
    {
        $userKeyValues = Be::getDb()->getKeyValues('SELECT id, `name` FROM `system_user` WHERE is_delete=0');
        $appKeyValues = Be::getService('System.App')->getAppNameLabelKeyValues();

        Be::getPlugin('Curd')->setting([
            'label' => '操作日志',
            'table' => 'system_op_log',
            'lists' => [
                'title' => '操作日志',
                'orderBy' => 'create_time',
                'orderByDir' => 'DESC',
                'form' => [
                    'items' => [
                        [
                            'name' => 'user_id',
                            'label' => '用户',
                            'driver' => FormItemSelect::class,
                            'keyValues' => $userKeyValues,
                        ],
                        [
                            'name' => 'app',
                            'label' => '应用',
                            'driver' => FormItemSelect::class,
                            'keyValues' => $appKeyValues,
                        ],
                        [
                            'name' => 'content',
                            'label' => '内容',
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
                            'url' => beUrl('System.OpLog.deleteLogs'),
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
                            'name' => 'user_id',
                            'label' => '用户',
                            'width' => '120',
                            'keyValues' => $userKeyValues,
                        ],
                        [
                            'name' => 'app',
                            'label' => '应用',
                            'width' => '120',
                            'keyValues' => $appKeyValues,
                        ],
                        [
                            'name' => 'route',
                            'label' => '访问路径',
                            'value' => function($row) {
                                return $row['app'] . '.' .$row['controller'] . '.' .$row['action'];
                            },
                            'width' => '240'
                        ],
                        [
                            'name' => 'content',
                            'label' => '内容',
                            'align' => 'left',
                        ],
                        [
                            'name' => 'ip',
                            'label' => 'IP地址',
                            'width' => '160',
                        ],
                        [
                            'name' => 'create_time',
                            'label' => '创建时间',
                            'width' => '150',
                        ],
                    ],
                ],

                'operation' => [
                    'label' => '操作',
                    'width' => '120',
                    'items' => [
                        [
                            'label' => '查看',
                            'task' => 'detail',
                            'ui' => [
                                'button' => [
                                    'icon' => 'el-icon-search',
                                    'type' => 'success'
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
                            'name' => 'user_id',
                            'label' => '用户',
                            'value' => function ($row) use ($userKeyValues) {
                                if (isset($userKeyValues[$row['user_id']])) {
                                    return $userKeyValues[$row['user_id']];
                                }
                                return '';
                            },
                        ],
                        [
                            'name' => 'app',
                            'label' => '应用名',
                        ],
                        [
                            'name' => 'controller',
                            'label' => '控制器名',
                        ],
                        [
                            'name' => 'action',
                            'label' => '动作名',
                        ],
                        [
                            'name' => 'content',
                            'label' => '内容',
                        ],
                        [
                            'name' => 'details',
                            'label' => '明细',
                        ],
                        [
                            'name' => 'ip',
                            'label' => 'IP地址',
                        ],
                        [
                            'name' => 'create_time',
                            'label' => '创建时间',
                        ],
                    ]
                ],
            ],
        ])->execute();
    }

    /**
     * 删除操作日志
     *
     * @BePermission("操作日志", ordering="10.31")
     */
    public function deleteLogs()
    {
        $db = Be::getDb();
        $db->startTransaction();
        try {
            Be::newTable('system_op_log')
                ->where('create_time', '<', date('Y-m-d H:i:s', time() - 90 * 86400))
                ->delete();
            beOpLog('删除三个月前操作日志！');
            $db->commit();
            Response::success('删除三个月前操作日志成功！');
        } catch (\Exception $e) {
            $db->rollback();
            Response::error($e->getMessage());
        }
    }

}