<?php

namespace Be\Mf\App\System\Controller;

use Be\Mf\Plugin\Table\Item\TableItemIcon;
use Be\Mf\Plugin\Table\Item\TableItemTag;
use Be\Mf\Be;


/**
 * @BeMenuGroup("管理")
 * @BePermissionGroup("管理")
 */
class Cache
{

    /**
     * @BeMenu("缓存", icon = "el-icon-fa fa-database", ordering="2.4")
     * @BePermission("缓存列表", ordering="2.4")
     */
    public function index()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();
        $serviceSystemLog = Be::getService('System.Cache');
        if ($request->isAjax()) {
            $tableData = $serviceSystemLog->getCategories();
            $response->set('success', true);
            $response->set('data', [
                'total' => 0,
                'tableData' => $tableData,
            ]);
            $response->json();
        } else {
            Be::getPlugin('Lists')->setting([
                'pageSize' => 10,
                'toolbar' => [
                    'items' => [
                        [
                            'label' => '清除所有缓存',
                            'action' => 'delete',
                            'confirm' => '确认要清除所有缓存么？',
                            'target' => 'ajax',
                            'ui' => [
                                'type' => 'danger'
                            ]
                        ],
                    ],
                ],

                'table' => [
                    'items' => [
                        [
                            'name' => 'icon',
                            'label' => '',
                            'driver' => TableItemIcon::class,
                            'width' => '60',
                        ],
                        [
                            'name' => 'name',
                            'label' => '缓存类型',
                            'driver' => TableItemTag::class,
                            'width' => '120',
                            'align' => 'left',
                        ],
                        [
                            'name' => 'label',
                            'label' => '缓存名称',
                            'width' => '120',
                            'align' => 'left',
                        ],
                        [
                            'name' => 'description',
                            'label' => '描述',
                            'align' => 'left',
                        ],
                        [
                            'name' => 'count',
                            'label' => '文件数',
                            'width' => '120',
                        ],
                        [
                            'name' => 'sizeStr',
                            'label' => '空间占用',
                            'width' => '120',
                        ],
                    ],
                    'ui' => [
                        'show-summary' => null,
                        ':summary-method' => 'getSummaries',
                    ]
                ],

                'operation' => [
                    'label' => '操作',
                    'width' => '120',
                    'items' => [
                        [
                            'label' => '清除',
                            'action' => 'delete',
                            'target' => 'ajax',
                            'confirm' => '确认要清除缓存么？',
                            'ui' => [
                                'type' => 'danger'
                            ]
                        ],
                    ]
                ],

                'vueMethods' => [
                    'getSummaries' => 'function(param) {
                        var summaries = [];
                        param.columns.forEach(function(column, index) {
                            if (index === 0) {
                                summaries[index] = "总计";
                                return;
                            }
                            
                            var total;
                            if (column.property == "count") {
                                total = 0;
                                param.data.forEach(function(x){
                                    total += Number(x.count);
                                })
                                summaries[index] = total;
                            } else if (column.property == "sizeStr") {
                                total = 0;
                                param.data.forEach(function(x){
                                    total += Number(x.size);
                                })

                                if (total < 1024) {
                                    total = total + " B";
                                } else if (total < (1024*1024)) {
                                    total = total / 1024;
                                    total = total.toFixed(2) + " KB";
                                } else if (total < (1024*1024*1024)) {
                                    total = total / (1024*1024);
                                    total = total.toFixed(2) + " MB";
                                } else {
                                    total = total / (1024*1024*1024);
                                    total = total.toFixed(2) + " GB";
                                }
                                
                                summaries[index] = total;
                            } else {
                                summaries[index] = "";
                            }
                        });
                        return summaries;
                    }',
                ],
            ])->execute();
        }
    }

    /**
     * @BePermission("删除缓存", ordering="2.41")
     */
    public function delete()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();
        try {
            $postData = $request->json();
            $name = $postData['row']['name'] ?? null;
            $serviceSystemCache = Be::getService('System.Cache');
            $serviceSystemCache->delete($name);
            beOpLog($name ? ('清除缓存（' . $name. '）') : '清除所有缓存' );
            $response->success('清除缓存成功！');
        } catch (\Exception $e) {
            $response->error($e->getMessage());
        }
    }


}