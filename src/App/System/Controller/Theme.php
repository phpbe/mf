<?php
namespace Be\Mf\App\System\Controller;

use Be\Mf\Plugin\Form\Item\FormItemDatePickerRange;
use Be\Mf\Be;
use Be\F\Db\Tuple;

/**
 * @BeMenuGroup("管理", icon="el-icon-fa fa-cube")
 * @BePermissionGroup("管理")
 */
class Theme
{

    /**
     * @BeMenu("主题", icon="el-icon-fa fa-cubes")
     * @BePermission("主题列表")
     */
    public function themes()
    {
        Be::getPlugin('Curd')->setting([

            'label' => '主题管理',
            'table' => 'system_theme',

            'lists' => [
                'title' => '已安装的主题列表',
                'form' => [
                    'items' => [
                        [
                            'name' => 'name',
                            'label' => '主题名',
                        ],
                        [
                            'name' => 'label',
                            'label' => '主题中文名',
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
                            'name' => 'name',
                            'label' => '主题名',
                            'width' => '120',
                            'align' => 'left',
                        ],
                        [
                            'name' => 'label',
                            'label' => '主题中文名',
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
                            'confirm' => '确认要卸载么？',
                            'target' => 'ajax',
                            'ui' => [
                                'type' => 'danger'
                            ]
                        ],
                    ]
                ],
            ],

            'edit' => [
                'title' => '编辑主题',
                'form' => [
                    'items' => [
                        [
                            'name' => 'name',
                            'label' => '主题名',
                            'disabled' => true,
                        ],
                        [
                            'name' => 'label',
                            'label' => '主题中文名',
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
     * 安装新主题
     *
     * @BePermission("安装主题")
     */
    public function install()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        if ($request->isAjax()) {
            $postData = $request->json();

            if (!isset($postData['formData']['themeName'])) {
                $response->error('参数主题名缺失！');
            }

            $themeName = $postData['formData']['themeName'];

            try {
                $serviceApp = Be::getService('System.Theme');
                $serviceApp->install($themeName);

                beOpLog('安装新主题：' . $themeName);
                $response->success('主题安装成功！');
            } catch (\Throwable $t) {
                $response->error($t->getMessage());
            }
        } else {
            Be::getPlugin('Form')
                ->setting([
                    'title' => '安装新主题',
                    'form' => [
                        'items' => [
                            [
                                'name' => 'themeName',
                                'label' => '主题名',
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
     * 卸载主题
     *
     * @BePermission("卸载主题")
     */
    public function uninstall()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $postData = $request->json();

        if (!isset($postData['row']['name'])) {
            $response->error('参数主题名缺失！');
        }

        $themeName = $postData['row']['name'];

        try {
            $serviceTheme = Be::getService('System.Theme');
            $serviceTheme->uninstall($themeName);

            beOpLog('卸载主题：' . $themeName);
            $response->success('主题卸载成功！');
        } catch (\Throwable $t) {
            $response->error($t->getMessage());
        }
    }


}

