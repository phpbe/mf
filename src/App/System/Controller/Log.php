<?php
namespace Be\App\System\Controller;

use Be\Plugin\Form\Item\FormItemRadioGroupButton;
use Be\System\Be;
use Be\System\Request;
use Be\System\Response;

/**
 * @BeMenuGroup("日志")
 * @BePermissionGroup("系统日志")
 */
class Log
{

    /**
     * 运行日志
     *
     * @BeMenu("系统日志", icon="el-icon-fa fa-video-camera", ordering="10.3")
     * @BePermission("系统日志", ordering="10.3")
     */
    public function lists()
    {
        $serviceSystemLog = Be::getService('System.Log');
        if (Request::isPost()) {
            $postData = Request::json();
            $formData = $postData['formData'];

            $year = $formData['year'];
            $month = $formData['month'];
            $day = $formData['day'];

            $total = $serviceSystemLog->getlogCount($year, $month, $day);

            $page = $postData['page'] ?? 1;
            $pageSize = $postData['pageSize'] ?? 10;

            $offset = ($page - 1) * $pageSize;
            if ($offset > $total) $offset = $total;

            $tableData = $serviceSystemLog->getlogs($year, $month, $day, $offset, $pageSize);

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

            if (!in_array($day, $days) && count($days) > 0) {
                $day = $days[0];
            }

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
                    ],
                    'ui' => [
                        'border' => true,
                    ]
                ],

                'operation' => [
                    'label' => '操作',
                    'width' => '120',
                    'items' => [
                        [
                            'label' => '查看',
                            'url' => beUrl('System.Log.detail'),
                            'target' => 'drawer',
                            'drawer' => [
                                'width' => '75%',
                            ],
                            'ui' => [
                                'link' => [
                                    'type' => 'primary'
                                ]
                            ]
                        ],
                        [
                            'label' => '删除',
                            'url' => beUrl('System.Log.delete'),
                            'confirm' => '确认要删除么？',
                            'target' => 'ajax',
                            'ui' => [
                                'link' => [
                                    'type' => 'danger'
                                ]
                            ]
                        ],
                    ]
                ],
            ])->execute();
        }
    }

    /**
     * 查看系统日志
     *
     * @BePermission("查看", ordering="10.3")
     */
    public function detail()
    {
        $year = Request::request('year');
        $month = Request::request('month');
        $day = Request::request('day');
        $hash = Request::request('hash');

        try {
            $servicebeOpLog = Be::getService('System.Log');
            $log = $servicebeOpLog->getlog($year, $month, $day, $hash);
            Response::setTitle('系统日志详情');
            Response::set('log', $log);
            Response::display();
        } catch (\Exception $e) {
            Response::error($e->getMessage());
        }
    }

    /**
     * 删除系统日志
     *
     * @BePermission("删除", ordering="10.3")
     */
    public function delete() {
        $year = Request::request('year', 0, 'int');
        $month = Request::request('month', 0, 'int');
        $day = Request::request('day', 0, 'int');

        try {
            $servicebeOpLog = Be::getService('System.Log');
            $servicebeOpLog->deleteLogs($year, $month, $day);
            Response::success('删除日志成功！');
        } catch (\Exception $e) {
            Response::error($e->getMessage());
        }
    }

}