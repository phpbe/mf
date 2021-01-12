<?php
namespace Be\Mf\App\System\Controller;

use Be\Framework\Plugin\Form\Item\FormItemDatePickerRange;
use Be\Framework\Plugin\Form\Item\FormItemInputNumberInt;
use Be\Framework\Plugin\Table\Item\TableItemIcon;
use Be\Mf\Be;
use Be\Framework\Db\Tuple;

/**
 * @BeMenuGroup("管理", icon="el-icon-fa fa-cube")
 * @BePermissionGroup("管理")
 */
class App
{

    /**
     * @BeMenu("应用", icon="el-icon-fa fa-cubes")
     * @BePermission("应用列表")
     */
    public function apps()
    {
        Be::getPlugin('Curd')->setting([

            'label' => '应用管理',
            'table' => 'system_app',

            'lists' => [
                'title' => '已安装的应用列表',
                'form' => [
                    'items' => [
                        [
                            'name' => 'name',
                            'label' => '应用名',
                        ],
                        [
                            'name' => 'label',
                            'label' => '应用中文名',
                        ],
                        [
                            'name' => 'install_time',
                            'label' => '安装时间',
                            'driver' => FormItemDatePickerRange::class,
                        ],
                    ],
                ],


                'toolbar' => [

                    'items' => [
                        [
                            'label' => '安装',
                            'action' => 'install',
                            'target' => 'drawer',
                            'ui' => [
                                'icon' => 'el-icon-plus',
                                'type' => 'primary',
                            ]
                        ],
                    ]
                ],

                'table' => [
                    'items' => [
                        [
                            'name' => 'id',
                            'label' => 'ID',
                            'width' => '90',
                        ],
                        [
                            'name' => 'icon',
                            'label' => '图标',
                            'driver' => TableItemIcon::class,
                            'width' => '90',
                        ],
                        [
                            'name' => 'name',
                            'label' => '应用名',
                            'width' => '120',
                            'align' => 'left',
                        ],
                        [
                            'name' => 'label',
                            'label' => '应用中文名',
                        ],
                        [
                            'name' => 'ordering',
                            'label' => '排序',
                            'width' => '90',
                        ],
                        [
                            'name' => 'install_time',
                            'label' => '安装时间',
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
                            'label' => '编辑',
                            'task' => 'edit',
                            'target' => 'drawer',
                            'ui' => [
                                'type' => 'primary'
                            ]
                        ],
                        [
                            'label' => '卸载',
                            'action' => 'uninstall',
                            'confirm' => '应用数据将被清除，且不可恢复，确认要卸载么？',
                            'target' => 'ajax',
                            'ui' => [
                                'type' => 'danger'
                            ]
                        ],
                    ]
                ],
            ],

            'edit' => [
                'title' => '编辑应用',
                'form' => [
                    'items' => [
                        [
                            'name' => 'name',
                            'label' => '应用名',
                            'disabled' => true,
                        ],
                        [
                            'name' => 'label',
                            'label' => '应用中文名',
                        ],
                        [
                            'name' => 'icon',
                            'label' => '图标',
                        ],
                        [
                            'name' => 'ordering',
                            'label' => '排序',
                            'driver' => FormItemInputNumberInt::class,
                        ],
                    ]
                ],
                'events' => [
                    'before' => function (Tuple &$tuple) {
                        $tuple->update_time = date('Y-m-d H:i:s');
                    }
                ]
            ],
        ])->execute();
    }

    /**
     * 安装新应用
     *
     * @BePermission("安装应用")
     */
    public function install()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();
        if ($request->isAjax()) {
            $postData = $request->json();

            if (!isset($postData['formData']['appName'])) {
                $response->error('参数应用名缺失！');
            }

            $appName = $postData['formData']['appName'];

            try {
                $serviceApp = Be::getService('System.App');
                $serviceApp->install($appName);

                beOpLog('安装新应用：' . $appName);
                $response->success('应用安装成功！');
            } catch (\Throwable $t) {
                $response->error($t->getMessage());
            }

        } else {
            Be::getPlugin('Form')
                ->setting([
                    'title' => '安装新应用',
                    'form' => [
                        'items' => [
                            [
                                'name' => 'appName',
                                'label' => '应用名',
                                'required' => true,
                            ],
                        ],
                        'actions' => [
                            'submit' => '安装',
                        ]
                    ],
                ])
                ->execute();
        }
    }

    /**
     * 卸载应用
     *
     * @BePermission("卸载应用")
     */
    public function uninstall()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $postData = $request->json();

        if (!isset($postData['row']['name'])) {
            $response->error('参数应用名缺失！');
        }

        $appName = $postData['row']['name'];

        try {
            $serviceApp = Be::getService('System.App');
            $serviceApp->uninstall($appName);

            beOpLog('卸载应用：' . $appName);
            $response->success('应用卸载成功！');
        } catch (\Throwable $t) {
            $response->error($t->getMessage());
        }
    }


}

