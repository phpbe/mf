<?php

namespace Be\App\System\Controller;

use Be\System\Be;
use Be\System\Response;
use Be\System\Controller;

class UserLoginLog extends Controller
{

    use \App\System\AdminTrait\Curd;

    public function __construct()
    {
        $this->config = [

            'name' => '管理员登陆日志',

            'table' => 'System.AdminUserLog',

            'action' => [

                'lists' => [

                    'search' => [

                        'content' => [
                            'name' => '内容',
                            'driver' => \Be\System\App\SearchItem\SearchItemString::class,
                            'uiType' => 'text',
                            'operation' => '%like%',
                        ],

                        'success' => [
                            'name' => '登录成功',
                            'driver' => \Be\System\App\SearchItem\SearchItemInt::class,
                            'uiType' => 'select',
                            'keyValues' => ':不限|0:失败|1:成功'
                        ]
                    ],


                    'toolbar' => [
                        [
                            'name' => '删除三个月前后台日志',
                            'url' => beUrl('System.AdminUserLog.deleteLogs'),
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

                'export' => [],
            ],
        ];
    }

    /**
     * 删除管理员登陆日志
     *
     * @be-action 删除管理员登陆日志
     * @be-menu 删除管理员登陆日志
     * @be-permission 删除管理员登陆日志
     */
    public function deleteLogs()
    {
        Be::getDb()->startTransaction();
        try {
            Be::getService('System.UserLoginLog')->deleteLogs();
            beSystemLog($this->config['name'] . '：删除三个月前管理员登陆日志日志！');

            Be::getDb()->commit();

            Response::success('删除管理员登陆日志成功！');
        } catch (\Exception $e) {

            Be::getDb()->rollback();
            Response::error($e->getMessage());
        }
    }

}

