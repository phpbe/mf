<?php
namespace Be\Mf\App\System\Controller;

use Be\Mf\Plugin\Form\Item\FormItemDatePickerRange;
use Be\Mf\Plugin\Form\Item\FormItemInputNumberInt;
use Be\Mf\Plugin\Table\Item\TableItemIcon;
use Be\Mf\Be;
use Be\F\Db\Tuple;

/**
 * @BeMenuGroup("管理", icon="el-icon-fa fa-cube", ordering="2")
 * @BePermissionGroup("管理", ordering="2")
 */
class App
{

    /**
     * @BeMenu("应用", icon="el-icon-fa fa-cubes", ordering="2.1")
     * @BePermission("应用列表", ordering="2.1")
     */
    public function apps()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();
        if ($request->isAjax()) {
            try {
                $postData = $request->json();
                $apps = Be::getService('System.App')->getApps();
                $page = $postData['page'];
                $pageSize = $postData['pageSize'];
                $tableData = array_slice($apps, ($page - 1) * $pageSize, $pageSize);
                $response->set('success', true);
                $response->set('data', [
                    'total' => count($apps),
                    'tableData' => $tableData,
                ]);
                $response->json();
            } catch (\Throwable $t) {
                $response->set('success', false);
                $response->set('message', $t->getMessage());
                $response->json();
                Be::getLog()->error($t);
            }

        } else {

            Be::getPlugin('Lists')
                ->setting([
                    'title' => '已安装的应用列表',

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
                        ],
                    ],

                    'operation' => [
                        'label' => '操作',
                        'width' => '120',
                        'items' => [
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
                ])
                ->display();

            $response->createHistory();
        }
    }

    /**
     * 安装新应用
     *
     * @BePermission("安装应用", ordering="2.11")
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
     * @BePermission("卸载应用", ordering="2.12")
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

