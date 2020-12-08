<?php
namespace Be\App\System\Controller;

use Be\Plugin\Form\Item\FormItemRadio;
use Be\Plugin\Form\Item\FormItemRadioGroupButton;
use Be\System\Be;
use Be\System\Request;
use Be\System\Response;

/**
 * @BeMenuGroup("日志")
 * @BePermissionGroup("运行日志")
 */
class Log
{


    /**
     * 运行日志
     *
     * @BeMenu("运行日志", icon="el-icon-fa fa-video-camera", ordering="10.3")
     * @BePermission("运行日志", ordering="10.3")
     */
    public function index()
    {
        $serviceSystemLog = Be::getService('System.Log');
        if (Request::isPost()) {
            $postData = Request::json();
            $formData = $postData['formData'];

            $year = $formData['year'];
            $month = $formData['month'];
            $day = $formData['day'];

            $total = $serviceSystemLog->getlogCount($year, $month, $day);
            $tableData = $serviceSystemLog->getlogs($year, $month, $day, 30, 10);

            Response::set('success', true);
            Response::set('data', [
                'total' => $total,
                'tableData' => $tableData,
            ]);
            Response::json();

        } else {

            $years = $serviceSystemLog->getYears();

            $year = date('Y');
            $month = date('m');
            $day = date('d');

            $months = $serviceSystemLog->getMonths($year);
            $days = $serviceSystemLog->getDays($year, $month);

            Be::getPlugin('Lists')->setting([
                'pageSize' => 10,
                'form' => [
                    'items' => [
                        [
                            'name' => 'year',
                            'label' => '年份',
                            'driver' => FormItemRadioGroupButton::class,
                            'values' => $years,
                            'value' => $year,
                            'ui' => [
                                'form-item' => [
                                    'style' => 'display:block',
                                ]
                            ],
                        ],
                        [
                            'name' => 'month',
                            'label' => '月份',
                            'driver' => FormItemRadioGroupButton::class,
                            'values' => $months,
                            'value' => $month,
                            'ui' => [
                                'form-item' => [
                                    'style' => 'display:block',
                                ]
                            ],
                        ],
                        [
                            'name' => 'day',
                            'label' => '日期',
                            'driver' => FormItemRadioGroupButton::class,
                            'values' => $days,
                            'value' => $day,
                            'ui' => [
                                'form-item' => [
                                    'style' => 'display:block',
                                ]
                            ],
                        ],
                    ],
                    'actions' => [
                        'submit' => [
                            'label' => '搜索',
                        ],
                        'reset' => [
                            'label' => '重置',
                        ],
                        'export' => [
                            'label' => '导出',
                        ]
                    ],
                ],

                'table' => [
                    'items' => [
                        [
                            'name' => 'type',
                            'label' => '类型',
                            'align' => 'center',
                        ],
                        [
                            'name' => 'file',
                            'label' => '文件',
                            'align' => 'left'
                        ],
                        [
                            'name' => 'line',
                            'label' => '行号',
                            'align' => 'center',
                        ],
                        [
                            'name' => 'message',
                            'label' => '错误信息',
                            'align' => 'left',
                        ],
                        [
                            'name' => 'create_time',
                            'label' => '产生时间',
                            'align' => 'left',
                        ],
                        [
                            'name' => 'record_time',
                            'label' => '首次产生时间',
                            'align' => 'left',
                        ],
                        [
                            'name' => 'operation',
                            'label' => '操作',
                            'align' => 'left',
                        ]
                    ],
                    'ui' => [
                        'border' => true,
                    ]
                ],
            ])->execute();

        }




        $year = Request::request('year', date('Y'));
        $month = Request::request('month', date('m'));
        $day = Request::request('day', date('d'));

        $limit = Request::post('limit', -1, 'int');
        if ($limit == -1) {
            $adminConfigSystem = Be::getConfig('System.Admin');
            $limit = $adminConfigSystem->limit;
        }

        Response::setTitle('系统运行日志列表');

        Response::set('years', $years);

        if (!$year && count($years)) $year = $years[0];

        if ($year && in_array($year, $years)) {
            Response::set('year', $year);

            $months = $serviceSystemLog->getMonths($year);
            Response::set('months', $months);

            if (!$month && count($months)) $month = $months[0];

            if ($month && in_array($month, $months)) {
                Response::set('month', $month);

                $days = $serviceSystemLog->getDays($year, $month);
                Response::set('days', $days);

                if (!$day && count($days)) $day = $days[0];

                if ($day && in_array($day, $days)) {
                    Response::set('day', $day);

                    $errorCount = $serviceSystemLog->getlogCount($year, $month, $day);
                    Response::set('logCount', $errorCount);


                    $logs = $serviceSystemLog->getlogs($year, $month, $day, $pagination->getOffset(), $limit);
                    Response::set('logs', $logs);
                }
            }
        }

        Response::display();
    }

    public function log()
    {
        $year = Request::request('year');
        $month = Request::request('month');
        $day = Request::request('day');
        $hash = Request::request('hash');

        try {
            $servicebeOpLog = Be::getService('System.SystemLog');
            $log = $servicebeOpLog->getlog($year, $month, $day, $hash);
            Response::setTitle('系统日志详情');
            Response::set('log', $log);
            Response::display();
        } catch (\Exception $e) {
            Response::error($e->getMessage());
        }
    }


    public function deleteLogs() {
        $year = Request::request('year', 0, 'int');
        $month = Request::request('month', 0, 'int');
        $day = Request::request('day', 0, 'int');

        try {
            $servicebeOpLog = Be::getService('System.SystemLog');
            $servicebeOpLog->deleteLogs($year, $month, $day);
            Response::success('删除日志成功！');
        } catch (\Exception $e) {
            Response::error($e->getMessage());
        }
    }

}