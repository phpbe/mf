<?php
namespace Be\App\System\Controller;

use Be\System\Be;
use Be\System\Controller;

// 友情链接
class Link extends Controller
{

    use \App\System\AdminTrait\Curd;



    public function __construct()
    {
        $this->config = [

            'name' => '友情链接',

            'table' => 'System.Link',

            'action' => [

                'lists' => [

                    'search' => [

                        'title' => [
                            'name' => '名称',
                            'driver' => \Be\System\App\SearchItem\SearchItemString::class,
                            'uiType' => 'text',
                            'operation' => '%like%',
                        ],

                        'block' => [
                            'name' => '状态',
                            'driver' => \Be\System\App\SearchItem\SearchItemInt::class,
                            'uiType' => 'select',
                            'keyValues' => ':不限|0:启用|1:禁用'
                        ]
                    ],

                    'toolbar' => [
                        [
                            'name' => '新建',
                            'action' => 'create',
                            'icon' => 'fa fa-plus-circle',
                        ],
                        [
                            'name' => '批量启用',
                            'action' => 'unblock',
                            'icon' => 'fa fa-check-circle',
                            'class' => 'text-success',
                        ],
                        [
                            'name' => '批量禁用',
                            'action' => 'block',
                            'icon' => 'fa fa-close-circle',
                            'class' => 'text-warning',
                        ],
                        [
                            'name' => '批量删除',
                            'action' => 'delete',
                            'icon' => 'fa fa-times-circle',
                            'class' => 'text-danger'
                        ],
                        [
                            'name' => '导出',
                            'action' => 'export',
                            'icon' => 'fa fa-array-circle-down',
                        ],
                    ],

                    'operation' => [
                        [
                            'name' => '查看',
                            'action' => 'detail',
                            'icon' => 'fa fa-search',
                        ],
                        [
                            'name' => '编辑',
                            'action' => 'edit',
                            'icon' => 'fa fa-edit',
                        ],
                        [
                            'name' => '启用',
                            'action' => 'unblock',
                            'icon' => 'fa fa-close',
                            'class' => 'text-warning',
                        ],
                        [
                            'name' => '禁用',
                            'action' => 'block',
                            'icon' => 'fa fa-check',
                            'class' => 'text-success',
                        ],
                        [
                            'name' => '删除',
                            'action' => 'delete',
                            'icon' => 'fa fa-remove',
                            'class' => 'text-danger'
                        ],
                    ],
                ],

                'detail' => [],

                'create' => [],

                'edit' => [],

                'block' => [],

                'unblock' => [],

                'delete' => [
                    'field' => 'is_delete',
                    'value' => 1
                ],

                'export' => [],
            ],
        ];
    }


    protected function afterCreate($tuple) {
        Be::getService('System.Link')->updateCache();
    }

    protected function afterEdit($tuple) {
        Be::getService('System.Link')->updateCache();
    }

    protected function afterBlock($tuple) {
        Be::getService('System.Link')->updateCache();
    }

    protected function afterUnblock($tuple) {
        Be::getService('System.Link')->updateCache();
    }

    protected function afterDelete($tuple) {
        Be::getService('System.Link')->updateCache();
    }

}

