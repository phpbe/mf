<?php

namespace Be\App\System\Controller;

use Be\Plugin\Table\Item\TableItemIcon;
use Be\Plugin\Table\Item\TableItemTag;
use Be\System\Be;
use Be\System\Request;
use Be\System\Response;


/**
 * @BeMenuGroup("缓存管理", icon = "el-icon-fa fa-database")
 * @BePermissionGroup("缓存管理")
 */
class Cache
{

    /**
     * @BeMenu("缓存管理")
     * @BePermission("缓存管理")
     */
    public function index()
    {
        $serviceSystemLog = Be::getService('System.Cache');
        if (Request::isAjax()) {
            $tableData = $serviceSystemLog->getCaches();
            Response::set('success', true);
            Response::set('data', [
                'total' => 0,
                'tableData' => $tableData,
            ]);
            Response::json();
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
                                'button' => [
                                    'type' => 'danger'
                                ]
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
                                'link' => [
                                    'type' => 'danger'
                                ]
                            ]
                        ],
                    ]
                ],

                'vueMethods' => [
                    'getSummaries' => 'function(param) {
                        console.log(param);
                        
                        var summaries = [];
                        param.columns.forEach(function(column, index) {
                            if (index === 0) {
                                summaries[index] = "总计";
                                return;
                            }
                            
                            var total;
                            if (column.property == "count") {
                                total = 0;
                                param.data.forEach(function(x, index){
                                    total += Number(x.count);
                                })
                                summaries[index] = total;
                            } else if (column.property == "sizeStr") {
                                total = 0;
                                param.data.forEach(function(x, index){
                                    total += Number(x.size);
                                })

                                if (total < 1024) {
                                    total = total + " B";
                                } else if (total < (1024*1024)) {
                                    var temp = total / 1024;
                                    temp = temp.toFixed(2);
                                    total = temp + " KB";
                                } else if (total < (1024*1024*1024)) {
                                    var temp = total / (1024*1024);
                                    temp = temp.toFixed(2);
                                    total = temp + " MB";
                                } else {
                                    var temp = total / (1024*1024*1024);
                                    temp = temp.toFixed(2);
                                    total = temp + " GB";
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
     * @BePermission("删除缓存")
     */
    public function delete()
    {
        try {
            $postData = Request::json();
            $name = $postData['row']['name'] ?? null;
            $serviceSystemCache = Be::getService('System.Cache');
            $serviceSystemCache->delete($name);
            beOpLog($name ? ('清除缓存（' . $name. '）') : '清除所有缓存' );
            Response::success('清除缓存成功！');
        } catch (\Exception $e) {
            Response::error($e->getMessage());
        }
    }


}