<?php

namespace Be\Mf\Plugin\Task;

use Be\F\Db\Tuple;
use Be\F\Util\Datetime;
use Be\Mf\Be;
use Be\Mf\Plugin\Detail\Item\DetailItemCode;
use Be\Mf\Plugin\Detail\Item\DetailItemSwitch;
use Be\Mf\Plugin\Driver;
use Be\Mf\Plugin\Form\Item\FormItemCode;
use Be\Mf\Plugin\Form\Item\FormItemCron;
use Be\Mf\Plugin\Form\Item\FormItemDatePickerRange;
use Be\Mf\Plugin\Form\Item\FormItemInputNumberInt;
use Be\Mf\Plugin\Form\Item\FormItemSelect;
use Be\Mf\Plugin\Form\Item\FormItemSwitch;
use Be\Mf\Plugin\Table\Item\TableItemCustom;
use Be\Mf\Plugin\Table\Item\TableItemLink;
use Be\Mf\Plugin\Table\Item\TableItemSelection;
use Be\Mf\Plugin\Table\Item\TableItemSwitch;
use Be\Mf\Task\Annotation\BeTask;

/**
 * 计划任务
 *
 * Class Task
 * @package Be\Mf\Plugin\Task
 */
class Task extends Driver
{

    private $loaded = [];

    /**
     * 执行指定任务
     *
     * @param string $task
     */
    public function execute($task = null)
    {
        $request = Be::getRequest();

        if ($task === null) {
            $task = $request->request('task', 'lists');
        }

        if (method_exists($this, $task)) {
            $this->$task();
            return;
        }

        $appName = isset($this->setting['appName']) ? $this->setting['appName'] : $request->getAppName();
        if (!isset($this->loaded[$appName])) {

            $db = Be::getDb();

            $sql = 'SELECT * FROM system_task WHERE app=' . $db->quoteValue($appName);
            $dbTasks = $db->getKeyObjects($sql, null, 'name');

            $dir = Be::getRuntime()->getRootPath() . Be::getProperty('App.' . $appName)->getPath() . '/Task';
            if (file_exists($dir) && is_dir($dir)) {
                $fileNames = scandir($dir);
                foreach ($fileNames as $fileName) {
                    if ($fileName != '.' && $fileName != '..' && is_file($dir . '/' . $fileName)) {
                        $taskName = substr($fileName, 0, -4);
                        $className = '\\Be\\Mf\\App\\' . $appName . '\\Task\\' . $taskName;
                        if (class_exists($className)) {
                            $reflection = new \ReflectionClass($className);
                            $classComment = $reflection->getDocComment();
                            $parseClassComments = \Be\F\Util\Annotation::parse($classComment);
                            if (isset($parseClassComments['BeTask'][0])) {
                                $annotation = new BeTask($parseClassComments['BeTask'][0]);
                                $task = $annotation->toArray();

                                $schedule = $task['schedule'] ?? '';
                                $scheduleLock = 0;
                                $defaultProperties = $reflection->getDefaultProperties();
                                if (isset($defaultProperties['schedule']) && $defaultProperties['schedule']) {
                                    $schedule = $defaultProperties['schedule'];
                                    $scheduleLock = 1;
                                }

                                if (isset($dbTasks[$taskName])) {
                                    $data = [
                                        'id' => $dbTasks[$taskName]->id,
                                        'name' => $taskName,
                                        'label' => $task['value'] ?? '',
                                        'schedule' => $schedule,
                                        'schedule_lock' => $scheduleLock,
                                        'is_delete' => 0,
                                        'update_time' => date('Y-m-d H:i:s'),
                                    ];
                                    $db->update('system_task', $data, 'id');
                                } else {
                                    $data = [
                                        'app' => $appName,
                                        'name' => $taskName,
                                        'label' => $task['value'] ?? '',
                                        'schedule' => $schedule,
                                        'schedule_lock' => $scheduleLock,
                                        'is_enable' => 0,
                                        'is_delete' => 0,
                                        'last_execute_time' => '0000-00-00 00:00:00',
                                        'create_time' => date('Y-m-d H:i:s'),
                                        'update_time' => date('Y-m-d H:i:s'),
                                    ];
                                    $db->insert('system_task', $data);
                                }
                            }
                        }
                    }
                }
            }

            $this->loaded[$appName] = 1;
        }


        Be::getPlugin('Curd')->setting([

            'label' => '计划任务',
            'table' => 'system_task',

            'lists' => [
                'title' => '计划任务',

                'filter' => [
                    ['app', '=', $appName],
                    ['is_delete', '=', '0'],
                ],

                'form' => [
                    'items' => [
                        [
                            'name' => 'name',
                            'label' => '类名',
                        ],
                        [
                            'name' => 'label',
                            'label' => '名称',
                        ],
                        [
                            'name' => 'is_enable',
                            'label' => '启用状态',
                            'driver' => FormItemSelect::class,
                            'keyValues' => [
                                '1' => '启用',
                                '0' => '禁用',
                            ],
                            'nullValue' => '-1',
                        ],
                        [
                            'name' => 'last_execute_time',
                            'label' => '最后执行时间',
                            'driver' => FormItemDatePickerRange::class,
                        ],
                    ],
                ],


                'toolbar' => [

                    'items' => [
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
                            'label' => '删除一个月前运行日志',
                            'task' => 'deleteLogs',
                            'target' => 'ajax',
                            'confirm' => '本操作为物理删除，不可恢复，确认要删除么？',
                            'ui' => [
                                'icon' => 'el-icon-fa fa-remove',
                                'type' => 'danger',
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
                            'sortable' => true,
                        ],
                        [
                            'name' => 'name',
                            'label' => '类名',
                            'driver' => TableItemLink::class,
                            'task' => 'detail',
                            'target' => 'drawer',
                        ],
                        [
                            'name' => 'label',
                            'label' => '名称',
                            'align' => 'left',
                        ],
                        [
                            'name' => 'schedule',
                            'label' => '执行计划',
                            'width' => '90',
                        ],
                        [
                            'name' => 'schedule_lock',
                            'label' => '执行计划锁',
                            'driver' => TableItemCustom::class,
                            'keyValues' => [
                                '1' => '<span class="el-tag el-tag--success el-tag--light el-tag--mini">锁定</span>',
                                '0' => '',
                            ],
                            'width' => '100',
                        ],
                        [
                            'name' => 'timeout',
                            'label' => '超时时间（秒）',
                            'width' => '120',
                        ],
                        [
                            'name' => 'last_execute_time',
                            'label' => '最后执行时间',
                            'width' => '150',
                            'sortable' => true,
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
                ],

                'operation' => [
                    'label' => '操作',
                    'width' => '150',
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
                            'label' => '运行',
                            'task' => 'run',
                            'target' => 'ajax',
                            'ui' => [
                                'type' => 'success'
                            ]
                        ],
                        [
                            'label' => '日志',
                            'task' => 'showLogs',
                            'target' => 'blank',
                            'ui' => [
                                'type' => 'info'
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
                            'label' => '类名',
                        ],
                        [
                            'name' => 'label',
                            'label' => '名称',
                        ],
                        [
                            'name' => 'schedule',
                            'label' => '执行计划',
                        ],
                        [
                            'name' => 'schedule_lock',
                            'label' => '执行计划锁定',
                            'driver' => DetailItemSwitch::class,
                        ],
                        [
                            'name' => 'timeout',
                            'label' => '超时时间（秒）',
                        ],
                        [
                            'name' => 'data',
                            'label' => '任务数据',
                            'driver' => DetailItemCode::class,
                            'language' => 'json',
                            'value' => function($row) {
                                if (!$row['data']) {
                                    return '{}';
                                } else {
                                    return $row['data'];
                                }
                            }
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

            'edit' => [
                'title' => '编辑计划任务',
                'form' => [
                    'items' => [
                        [
                            'name' => 'name',
                            'label' => '类名',
                            'readonly' => true,
                        ],
                        [
                            'name' => 'label',
                            'label' => '名称',
                            'readonly' => true,
                        ],
                        [
                            'name' => 'schedule',
                            'label' => '执行计划',
                            'driver' => FormItemCron::class,
                            'ui' => function($row) {
                                return [
                                    'form-item' => [
                                        'v-if' => $row['schedule_lock'] == '0' ? 'true' : 'false'
                                    ]
                                ];
                            }
                        ],
                        [
                            'name' => 'timeout',
                            'label' => '超时时间（秒）',
                            'driver' => FormItemInputNumberInt::class,
                        ],
                        [
                            'name' => 'data',
                            'label' => '任务数据',
                            'driver' => FormItemCode::class,
                            'language' => 'json',
                            'value' => function($row) {
                                if (!$row['data']) {
                                    return '{}';
                                } else {
                                    return $row['data'];
                                }
                            }
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
                    }
                ],
            ],

        ])->execute();
    }

    /**
     * 手动运行
     */
    public function run()
    {
        $response = Be::getResponse();
        try {
            $request = Be::getRequest();
            $postData = $request->json();
            $task = Be::getTuple('system_task');
            $task->load($postData['row']['id']);
            $task->trigger = 'MANUAL';
            Be::getRuntime()->getHttpServer()->getSwooleHttpServer()->task($task);
            beOpLog('手工启动任务：' . $task->label . '（' . $task->app . '.' . $task->name . '）');
            $response->success('任务启动成功！');
        } catch (\Throwable $t) {
            $response->error($t->getMessage());
            Be::getLog()->error($t);
        }
    }


    /**
     * 计划任务日志列表
     */
    public function showLogs()
    {
        $request = Be::getRequest();
        $postData = $request->post('data', '', '');
        $postData = json_decode($postData, true);
        $taskId = $postData['row']['id'];

        $url = beUrl(null, ['task'=>'logs', 'task_id' => $taskId]);
        $response = Be::getResponse();
        $response->redirect($url);
    }

    /**
     * 计划任务日志列表
     */
    public function logs()
    {
        $request = Be::getRequest();
        $taskId = $request->get('task_id', 0);

        $statusKeyValues = [
            'RUNNING' => '运行中',
            'COMPLETE' => '执行完成',
            'ERROR' => '出错',
        ];

        $triggerKeyValues = [
            'SYSTEM' => '系统调度',
            'MANUAL' => '人工启动',
            'RELATED' => '关联启动',
        ];

        Be::getPlugin('Curd')->setting([
            'label' => '计划任务日志',
            'table' => 'system_task_log',

            'lists' => [
                'title' => '计划任务日志列表',
                'orderBy' => 'id',
                'orderByDir' => 'DESC',
                'filter' => [
                    ['task_id', '=', $taskId],
                ],

                'form' => [
                    'items' => [
                        [
                            'name' => 'status',
                            'label' => '状态',
                            'driver' => FormItemSelect::class,
                            'keyValues' => array_merge(['' => '所有'], $statusKeyValues)
                        ],
                        [
                            'name' => 'message',
                            'label' => '异常信息',
                        ],
                        [
                            'name' => 'trigger',
                            'label' => '触发方式',
                            'driver' => FormItemSelect::class,
                            'keyValues' => array_merge(['' => '所有'], $triggerKeyValues)
                        ],
                        [
                            'name' => 'complete_time',
                            'label' => '完成时间',
                            'driver' => FormItemDatePickerRange::class,
                        ],
                        [
                            'name' => 'create_time',
                            'label' => '创建时间',
                            'driver' => FormItemDatePickerRange::class,
                        ],
                    ],
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
                            'sortable' => true,
                        ],
                        [
                            'name' => 'status',
                            'label' => '状态',
                            'width' => '90',
                            'driver' => TableItemCustom::class,
                            'keyValues' => [
                                'RUNNING' => '<span class="el-tag el-tag--primary el-tag--light el-tag--mini">运行中</span>',
                                'COMPLETE' => '<span class="el-tag el-tag--success el-tag--light el-tag--mini">执行完成</span>',
                                'ERROR' => '<span class="el-tag el-tag--danger el-tag--light el-tag--mini">出错</span>',
                            ],
                        ],
                        [
                            'name' => 'message',
                            'label' => '异常信息',
                            'align' => 'left',
                        ],
                        [
                            'name' => 'trigger',
                            'label' => '触发方式',
                            'keyValues' => $triggerKeyValues,
                            'width' => '90',
                        ],
                        [
                            'name' => 'complete_time',
                            'label' => '完成时间',
                            'width' => '150',
                            'sortable' => true,
                        ],
                        [
                            'name' => 'create_time',
                            'label' => '创建时间',
                            'width' => '150',
                            'sortable' => true,
                        ],
                        [
                            'name' => 'update_time',
                            'label' => '更新时间',
                            'width' => '150',
                            'sortable' => true,
                        ],
                    ],
                ],

                'operation' => [
                    'label' => '操作',
                    'width' => '120',
                    'items' => [
                        [
                            'label' => '查看明细',
                            'url' => beUrl(null, ['task' => 'logDetail']),
                            'target' => 'drawer',
                        ],
                        [
                            'label' => '删除',
                            'url' => beUrl(null, ['task' => 'deleteLog']),
                            'target' => 'ajax',
                            'confirm' => '本操作为物理删除，不可恢复，确认要删除么？',
                            'ui' => [
                                'type' => 'danger'
                            ]
                        ],
                    ]
                ],

            ],
        ])->execute('lists');
    }


    /**
     * 计划任务日志明细
     */
    public function logDetail()
    {

        $statusKeyValues = [
            'RUNNING' => '运行中',
            'COMPLETE' => '执行完成',
            'ERROR' => '出错',
        ];

        $triggerKeyValues = [
            'SYSTEM' => '系统调度',
            'MANUAL' => '人工启动',
            'RELATED' => '关联启动',
        ];

        Be::getPlugin('Curd')->setting([
            'label' => '计划任务日志',
            'table' => 'system_task_log',

            'lists' => [],

            'detail' => [
                'form' => [
                    'items' => [
                        [
                            'name' => 'id',
                            'label' => 'ID',
                        ],
                        [
                            'name' => 'data',
                            'label' => '任务数据',
                            'driver' => DetailItemCode::class,
                            'language' => 'json',
                            'value' => function($row) {
                                if (!$row['data']) {
                                    return '{}';
                                } else {
                                    return $row['data'];
                                }
                            }
                        ],
                        [
                            'name' => 'status',
                            'label' => '状态',
                            'keyValues' => $statusKeyValues,
                        ],
                        [
                            'name' => 'message',
                            'label' => '异常信息',
                        ],
                        [
                            'name' => 'trigger',
                            'label' => '触发方式',
                            'keyValues' => $triggerKeyValues,
                        ],
                        [
                            'name' => 'complete_time',
                            'label' => '完成时间',
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

        ])->execute('detail');
    }

    /**
     * 删除一条计划任务日志
     */
    public function deleteLog()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();
        try {
            $postData = $request->json();
            $tuple = Be::newTuple('system_task_log');
            $tuple->load($postData['row']['id']);
            $taskId = $tuple->task_id;
            $taskLogId = $tuple->id;
            $tuple->delete();
            beOpLog('删除了一条计划任务（#'.$taskId.'）日志（#'.$taskLogId.'）。');
            $response->success('删除计划任务日志成功！');
        } catch (\Throwable $t) {
            $response->error($t->getMessage());
            Be::getLog()->error($t);
        }
    }

    /**
     * 删除一个月前计划任务日志
     */
    public function deleteLogs()
    {
        $response = Be::getResponse();
        try {
            $lastMonth = Datetime::getLastMonth(date('Y-m-d H:i:s'));
            Be::newTable('system_task_log')
                ->where('create_time', '<', $lastMonth)
                ->delete();
            beOpLog('删除了一个月前计划任务日志。');
            $response->success('删除一个月前计划任务日志成功！');
        } catch (\Throwable $t) {
            $response->error($t->getMessage());
            Be::getLog()->error($t);
        }
    }
}

