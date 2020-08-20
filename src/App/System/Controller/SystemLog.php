<?php

namespace Be\App\System\Controller;

use Be\Plugin\Curd\SearchItem\SearchItemDatePickerRange;
use Be\Plugin\Curd\SearchItem\SearchItemInput;
use Be\Plugin\Curd\SearchItem\SearchItemSelect;
use Be\System\Be;
use Be\System\Response;

/**
 * @BeMenuGroup("日志", icon="el-icon-info")
 * @BePermissionGroup("日志")
 */
class SystemLog extends \Be\System\Controller
{

    /**
     * 系统日志
     *
     * @BeMenu("操作日志", icon="el-icon-finished")
     * @BePermission("查看操作日志")
     */
    public function logs()
    {
        $userKeyValues = Be::getDb()->getKeyValues('SELECT id, `name` FROM `system_user` WHERE is_delete=0');

        Be::getPlugin('Curd')->execute([
            'label' => '操作日志',
            'table' => 'system_log',
            'lists' => [
                'title' => '操作日志',
                'orderBy' => 'create_time',
                'orderByDir' => 'DESC',
                'search' => [
                    'items' => [
                        [
                            'name' => 'user_id',
                            'label' => '用户',
                            'driver' => SearchItemSelect::class,
                            'keyValues' => $userKeyValues,
                        ],
                        [
                            'name' => 'content',
                            'label' => '内容',
                            'driver' => SearchItemInput::class,
                            'op' => '%LIKE%',
                        ],
                        [
                            'name' => 'create_time',
                            'label' => '创建时间',
                            'driver' => SearchItemDatePickerRange::class,
                        ],
                    ],
                ],

                'toolbar' => [
                    'items' => [
                        [
                            'label' => '删除三个月前系统日志',
                            'url' => beUrl('System.SystemLog.deleteLogs'),
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

                'field' => [

                    // 未指定时取表的所有字段
                    'items' => [
                        [
                            'name' => 'user_id',
                            'label' => '用户',
                            'width' => '120',
                            'keyValues' => $userKeyValues,
                        ],
                        [
                            'name' => 'content',
                            'label' => '内容',
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

            'export' => [],
        ]);
    }

    /**
     * 删除操作日志
     *
     * @BePermission("删除操作日志")
     */
    public function deleteLogs()
    {
        $db = Be::getDb();
        $db->startTransaction();
        try {
            Be::newTable('system_log')
                ->where('create_time', '<', date('Y-m-d H:i:s', time() - 90 * 86400))
                ->delete();
            beSystemLog('删除三个月前操作日志！');
            $db->commit();
            Response::success('删除三个月前操作日志成功！');
        } catch (\Exception $e) {
            $db->rollback();
            Response::error($e->getMessage());
        }
    }

}