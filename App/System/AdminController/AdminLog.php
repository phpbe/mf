<?php

namespace App\System\AdminController;

use Be\System\Be;
use Be\System\Response;

/**
 * @be-controller 后台日志
 */
class AdminLog extends \Be\System\AdminController
{

    use \App\System\AdminTrait\Curd;

    public function __construct()
    {
        $this->config = [

            'name' => '后台日志',

            'table' => ['System', 'AdminLog'],

            'action' => [

                'lists' => [

                    'search' => [

                        'content' => [
                            'name' => '内容',
                            'driver' => \Be\System\App\SearchItem\SearchItemString::class,
                            'uiType' => 'text',
                            'operation' => '%like%',
                        ],

                        'user_id' => [
                            'name' => '用户',
                            'driver' => \Be\System\App\SearchItem\SearchItemInt::class,
                            'uiType' => 'select',
                            'keyValues' => Be::newTable('system_admin_user')->withCache(600)->getKeyValues('user_id', 'user_name')
                        ]
                    ],

                    'toolbar' => [
                        [
                            'name' => '删除三个月前后台日志',
                            'url' => adminUrl('System', 'AdminLog', 'deleteLogs'),
                            'icon' => 'fa fa-times-circle',
                            'class' => 'text-danger',
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
                    ],
                ],

                'detail' => [],
            ],

            'export' => [],

        ];
    }

    /**
     * 删除后台日志
     *
     * @be-action 删除后台日志
     * @be-permission
     */
    public function deleteLogs()
    {
        Be::getDb()->startTransaction();
        try {
            Be::getService('System', 'AdminLog')->deleteLogs();
            Be::getService('System', 'AdminLog')->addLog($this->config['name'] . '：删除三个月前后台日志！');

            Be::getDb()->commit();

            Response::success('删除后台日志成功！');
        } catch (\Exception $e) {

            Be::getDb()->rollback();
            Response::error($e->getMessage());
        }
    }

}