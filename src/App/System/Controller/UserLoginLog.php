<?php

namespace Be\App\System\Controller;

use Be\Plugin\Curd\FieldItem\FieldItemCustom;
use Be\Plugin\Curd\FieldItem\FieldItemSwitch;
use Be\Plugin\Curd\FieldItem\FieldItemTag;
use Be\Plugin\Curd\SearchItem\SearchItemDatePickerRange;
use Be\Plugin\Curd\SearchItem\SearchItemSelect;
use Be\System\Be;
use Be\System\Response;
use Be\System\Controller;

/**
 * @BeMenuGroup("日志", icon="el-icon-info")
 * @BePermissionGroup("日志")
 */
class UserLoginLog extends Controller
{

    /**
     * 系统日志
     *
     * @BeMenu("用户登录日志", icon="el-icon-finished")
     * @BePermission("查看用户登录日志")
     */
    public function logs()
    {

        Be::getPlugin('Curd')->execute([
            'label' => '用户登录日志',
            'table' => 'system_user_login_log',
            'lists' => [
                'title' => '用户登录日志',
                'orderBy' => 'create_time',
                'orderByDir' => 'DESC',
                'search' => [
                    'items' => [
                        [
                            'name' => 'username',
                            'label' => '用户名',
                            'op' => '%LIKE%',
                        ],
                        [
                            'name' => 'success',
                            'label' => '登录结果',
                            'driver' => SearchItemSelect::class,
                            'keyValues' => [
                                '' => '不限',
                                '0' => '失败',
                                '1' => '成功',
                            ],
                        ],
                        [
                            'name' => 'description',
                            'label' => '描述',
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
                            'url' => beUrl('System.UserLoginLog.deleteLogs'),
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
                            'name' => 'username',
                            'label' => '用户名',
                            'width' => '120',
                        ],
                        [
                            'name' => 'success',
                            'label' => '登录结果',
                            'driver' => FieldItemCustom::class,
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

            'export' => [],
        ]);
    }

    /**
     * 删除用户登录日志
     *
     * @BePermission("删除用户登录日志")
     */
    public function deleteLogs()
    {
        $db = Be::getDb();
        $db->startTransaction();
        try {
            Be::newTable('system_user_login_log')
                ->where('create_time', '<', date('Y-m-d H:i:s', time() - 90 * 86400))
                ->delete();
            beSystemLog('删除三个月前用户登录日志！');
            $db->commit();
            Response::success('删除三个月前用户登录日志成功！');
        } catch (\Exception $e) {
            $db->rollback();
            Response::error($e->getMessage());
        }
    }

}

