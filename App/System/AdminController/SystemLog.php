<?php
namespace App\System\AdminController;

use Be\System\Be;
use Be\System\Request;
use Be\System\Response;

class SystemLog extends \Be\System\AdminController
{


    // 系统日志
    public function logs()
    {
        $year = Request::request('year', date('Y'));
        $month = Request::request('month', date('m'));
        $day = Request::request('day', date('d'));

        $limit = Request::post('limit', -1, 'int');
        if ($limit == -1) {
            $adminConfigSystem = Be::getConfig('System', 'Admin');
            $limit = $adminConfigSystem->limit;
        }

        Response::setTitle('系统日志列表');

        $serviceSystemLog = Be::getService('System', 'SystemLog');
        $years = $serviceSystemLog->getYears();
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

                    $pagination = Be::getUi('Pagination');
                    $pagination->setLimit($limit);
                    $pagination->setTotal($errorCount);
                    $pagination->setPage(Request::request('page', 1, 'int'));
                    Response::set('pagination', $pagination);

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
            $serviceSystemLog = Be::getService('System', 'SystemLog');
            $log = $serviceSystemLog->getlog($year, $month, $day, $hash);
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
            $serviceSystemLog = Be::getService('System', 'SystemLog');
            $serviceSystemLog->deleteLogs($year, $month, $day);
            Response::success('删除日志成功！');
        } catch (\Exception $e) {
            Response::error($e->getMessage());
        }
    }

}