<?php

namespace Be\Mf\Plugin\Task;

use Be\F\Db\Tuple;
use Be\Mf\Be;
use Be\Mf\Plugin\Detail\Item\DetailItemSwitch;
use Be\Mf\Plugin\Driver;
use Be\Mf\Plugin\Form\Item\FormItemCron;
use Be\Mf\Plugin\Form\Item\FormItemDatePickerRange;
use Be\Mf\Plugin\Form\Item\FormItemSwitch;
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

                                if (isset($dbTasks[$taskName])) {
                                    $data = [
                                        'id' => $dbTasks[$taskName]->id,
                                        'name' => $taskName,
                                        'label' => $task['value'] ?? '',
                                        'schedule' => $task['schedule'] ?? '',
                                        'is_delete' => 0,
                                        'update_time' => date('Y-m-d H:i:s'),
                                    ];
                                    $db->update('system_task', $data, 'id');
                                } else {
                                    $data = [
                                        'app' => $appName,
                                        'name' => $taskName,
                                        'label' => $task['value'] ?? '',
                                        'schedule' => $task['schedule'] ?? '',
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

            'label' => '许划任务',
            'table' => 'system_task',

            'lists' => [
                'title' => '许划任务',

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
                            'label' => '类名',
                        ],
                        [
                            'name' => 'label',
                            'label' => '名称',
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
                    ],
                ],

                'operation' => [
                    'label' => '操作',
                    'width' => '150',
                    'items' => [
                        [
                            'label' => '查看',
                            'task' => 'detail',
                            'target' => 'drawer',
                            'ui' => [
                                'type' => 'primary'
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
                            'label' => '运行',
                            'task' => 'run',
                            'target' => 'ajax',
                            'ui' => [
                                'type' => 'success'
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

            'edit' => [
                'title' => '编辑计划任务',
                'form' => [
                    'items' => [
                        [
                            'name' => 'name',
                            'label' => '名称',
                            'readonly' => true,
                        ],
                        [
                            'name' => 'driver',
                            'label' => '驱动	',
                            'readonly' => true,
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
            $task->loadBy($postData['row']['id']);
            $task->trigger = 'MANUAL';
            Be::getRuntime()->getHttpServer()->getSwooleHttpServer()->task($task);
            beOpLog('手工启动任务：' . $task->label . '（' . $task->app . '.' . $task->name . '）');
            $response->success('任务启动成功！');
        } catch (\Throwable $t) {
            $response->error($t->getMessage());
        }
    }
}

