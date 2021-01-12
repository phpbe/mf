<?php

namespace Be\Mf\App\System\Controller;

use Be\Framework\Plugin\Detail\Item\DetailItemSwitch;
use Be\Framework\Plugin\Form\Item\FormItemCron;
use Be\Framework\Plugin\Form\Item\FormItemDatePickerRange;
use Be\Framework\Plugin\Form\Item\FormItemSwitch;
use Be\Framework\Plugin\Table\Item\TableItemSelection;
use Be\Framework\Plugin\Table\Item\TableItemSwitch;
use Be\Mf\Be;
use Be\Framework\Db\Tuple;


/**
 * @BeMenuGroup("管理")
 * @BePermissionGroup("管理")
 */
class Task
{

    /**
     * 计划任务
     *
     * @BeMenu("计划任务")
     * @BePermission("计划任务列表")
     */
    public function tasks()
    {
        Be::getPlugin('Curd')->setting([

            'label' => '许划任务',
            'table' => 'system_task',

            'lists' => [
                'title' => '许划任务',
                'form' => [
                    'items' => [
                        [
                            'name' => 'name',
                            'label' => '名称',
                        ],
                        [
                            'name' => 'driver',
                            'label' => '驱动	',
                        ],
                        [
                            'name' => 'last_execute_time',
                            'label' => '最后执行时间	',
                            'driver' => FormItemDatePickerRange::class,
                        ],
                        [
                            'name' => 'create_time',
                            'label' => '创建时间	',
                            'driver' => FormItemDatePickerRange::class,
                        ],
                    ],
                ],


                'toolbar' => [

                    'items' => [
                        [
                            'label' => '新建许划任务',
                            'task' => 'create',
                            'target' => 'drawer',
                            'ui' => [
                                'icon' => 'el-icon-plus',
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
                            'task' => 'delete',
                            'target' => 'ajax',
                            'ui' => [
                                'icon' => 'el-icon-delete',
                                'type' => 'danger'
                            ]
                        ],
                        [
                            'label' => '部署',
                            'action' => 'tutorial',
                            'target' => 'drawer',
                            'ui' => [
                                'icon' => 'el-icon-helper',
                            ]
                        ],
                    ]
                ],

                'table' => [
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
                            'name' => 'name',
                            'label' => '名称',
                        ],
                        [
                            'name' => 'driver',
                            'label' => '驱动	',
                        ],
                        [
                            'name' => 'schedule',
                            'label' => '执行计划',
                            'width' => '90',
                        ],
                        [
                            'name' => 'last_execute_time',
                            'label' => '最后执行时间',
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
                        [
                            'name' => 'create_time',
                            'label' => '创建时间',
                            'width' => '150',
                        ],
                        [
                            'name' => 'update_time',
                            'label' => '更新时间',
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
                            'target' => 'drawer',
                            'ui' => [
                                'type' => 'success'
                            ]
                        ],
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
                            'target' => 'ajax',
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
                            'name' => 'name',
                            'label' => '名称',
                        ],
                        [
                            'name' => 'driver',
                            'label' => '驱动	',
                        ],
                        [
                            'name' => 'schedule',
                            'label' => '执行计划',
                        ],
                        [
                            'name' => 'is_enable',
                            'label' => '启用/禁用',
                            'driver' => DetailItemSwitch::class,
                        ],
                        [
                            'name' => 'last_execute_time',
                            'label' => '最后执行时间',
                        ],
                        [
                            'name' => 'create_time',
                            'label' => '创建时间',
                        ],
                        [
                            'name' => 'update_time',
                            'label' => '更新时间',
                        ],
                    ]
                ],
            ],

            'create' => [
                'title' => '新建计划任务',
                'form' => [
                    'items' => [
                        [
                            'name' => 'name',
                            'label' => '名称',
                        ],
                        [
                            'name' => 'driver',
                            'label' => '驱动	',
                        ],
                        [
                            'name' => 'schedule',
                            'label' => '执行计划',
                            'driver' => FormItemCron::class,
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
                    'before' => function (Tuple $tuple) {
                        $tuple->create_time = date('Y-m-d H:i:s');
                        $tuple->update_time = date('Y-m-d H:i:s');
                    },
                ],
            ],

            'edit' => [
                'title' => '编辑计划任务',
                'form' => [
                    'items' => [
                        [
                            'name' => 'name',
                            'label' => '名称',
                        ],
                        [
                            'name' => 'driver',
                            'label' => '驱动	',
                        ],
                        [
                            'name' => 'schedule',
                            'label' => '执行计划',
                            'driver' => FormItemCron::class,
                        ],
                        [
                            'name' => 'is_enable',
                            'label' => '启用/禁用',
                            'driver' => FormItemSwitch::class,
                        ],
                    ]
                ],
                'events' => [
                    'before' => function (Tuple $tuple) {
                        $tuple->update_time = date('Y-m-d H:i:s');
                    }
                ]
            ],

            'fieldEdit' => [
                'events' => [
                    'before' => function (Tuple $tuple) {
                        $tuple->update_time = date('Y-m-d H:i:s');
                    },
                ],
            ],

        ])->execute();

    }

    /**
     * 部署
     */
    public function tutorial()
    {

    }

    /**
     * 侓康检查
     */
    public function health()
    {

        $lastTaskLog = Be::newTable('system_task_log')
            ->orderBy('id', 'DESC')
            ->getObject();
    }

    /**
     * 执行计划任务调度
     */
    public function dispatch()
    {
        // 抽取任务
        $extractTasks = Be::newTable('system_task')
            ->where('is_enable', 1)
            ->where('schedule', '!=', '')
            ->getObjects();
        //print_r($extractTasks);

        $t = time();
        foreach ($extractTasks as $extractTask) {
            $url = beUrl('System.Task.run', ['id' => $extractTask->id, 't' => $t]);
            echo $url . '<br>';
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HEADER, 1);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_TIMEOUT, 1);
            curl_exec($curl);
            curl_close($curl);
        }

        echo '-';
    }


    /**
     * 执行计划任务调度
     */
    public function run()
    {

    }

}
