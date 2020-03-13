<?php
namespace Be\App\System\Service;

use Be\System\Be;
use Be\System\Service\ServiceException;

class RuntimeLog extends \Be\System\Service
{

    /**
     * 获取日志年份列表
     *
     * @return array
     */
    public function getYears()
    {
        $dir = Be::getRuntime()->getDataPath() . '/System/RuntimeLog';
        $years = array();
        if (file_exists($dir) && is_dir($dir)) {
            $fileNames = scandir($dir);
            foreach ($fileNames as $fileName) {
                if ($fileName != '.' && $fileName != '..' && is_dir($dir . '/' . $fileName)) {
                    $years[] = $fileName;
                }
            }
        }
        return $years;
    }

    /**
     * 获取日志月份列表
     *
     * @param int $year 年
     * @return array
     */
    public function getMonths($year)
    {
        $dir = Be::getRuntime()->getDataPath() . '/System/RuntimeLog/' .  $year;
        $months = array();
        if (file_exists($dir) && is_dir($dir)) {
            $fileNames = scandir($dir);
            foreach ($fileNames as $fileName) {
                if ($fileName != '.' && $fileName != '..' && is_dir($dir . '/' . $fileName)) {
                    $months[] = $fileName;
                }
            }
        }
        return $months;
    }

    /**
     * 获取日志日期列表
     *
     * @param int $year 年
     * @param int $month 月
     * @return array
     */
    public function getDays($year, $month)
    {
        $dir = Be::getRuntime()->getDataPath() . '/System/RuntimeLog/' .  $year . '/' . $month;
        $days = array();
        if (file_exists($dir) && is_dir($dir)) {
            $fileNames = scandir($dir);
            foreach ($fileNames as $fileName) {
                if ($fileName != '.' && $fileName != '..' && is_dir($dir . '/' . $fileName)) {
                    $days[] = $fileName;
                }
            }
        }
        return $days;
    }

    /**
     * 获取指定日期的日志列表
     * @param int $year 年
     * @param int $month 月
     * @param int $day 日
     * @param int $offset 分面偏移量
     * @param int $limit 分页大小
     * @return array
     */
    public function getLogs($year, $month, $day, $offset = 0, $limit = 100)
    {
        $dataDir = Be::getRuntime()->getDataPath() . '/System/RuntimeLog/' .  $year . '/' . $month . '/' . $day . '/';
        $indexPath = Be::getRuntime()->getDataPath() . '/System/RuntimeLog/' .  $year . '/' . $month . '/' . $day . '/index';
        if (!is_file($indexPath)) return array();

        if ($offset < 0) $offset = 0;
        if ($limit <= 0) $limit = 20;
        if ($limit > 500) $limit = 500;

        $max = intval(filesize($indexPath) / 36) - 1;
        if ($max < 0) return array();

        $from = $offset;
        $to = $offset + $limit - 1;

        if ($from > $max) $from = $max;
        if ($to > $max) $to = $max;

        $fIndex = fopen($indexPath, 'rb');
        if (!$fIndex) return array();

        $logs = array();
        for ($i = $from; $i <= $to; $i++) {
            fseek($fIndex, $i * 20);

            $dataHashName = implode('', unpack('H*', fread($fIndex, 32)));
            $createTime = intval(implode('', unpack('L', fread($fIndex, 4))));

            $data = file_get_contents($dataDir . $dataHashName);

            $data = json_decode($data);
            $data['create_time'] = date('Y-m-d H:i:s', $createTime);
            $this->formatLog($data);
            $logs[] = $data;
        }
        fclose($fIndex);
        return $logs;
    }

    /**
     * 获取指定日期的日志总数
     *
     * @param int $year 年
     * @param int $month 月
     * @param int $day 日
     * @return int
     */
    public function getLogCount($year, $month, $day)
    {
        $path = Be::getRuntime()->getDataPath() . '/System/RuntimeLog/' .  $year . '/' . $month . '/' . $day . '/index';
        if (!is_file($path)) return 0;
        return intval(filesize($path) / 36);
    }

    /**
     * 获取指定日期和索引的日志明细
     * @param int $year 年
     * @param int $month 月
     * @param int $day 日
     * @param string $hashName 日志的 hash 文件名
     * @return array
     * @throws ServiceException
     */
    public function getLog($year, $month, $day, $hashName)
    {
        $dataPath = Be::getRuntime()->getDataPath() . '/System/RuntimeLog/' .  $year . '/' . $month . '/' . $day . '/'.$hashName;
        if (!is_file($dataPath)) {
            throw new ServiceException('打开日志数据文件不存在！');
        }

        $data = file_get_contents($dataPath);
        $data = json_decode($data);
        $this->formatLog($data);
        return $data;
    }

    /**
     * 格式化日志
     *
     * @param array $log 日志
     */
    public function formatLog(&$log) {

        if (!isset($log['file'])) {
            $file = '';
            if (isset($log['extra']['file'])) {
                $file = $log['extra']['file'];
            }
            $log['file'] = $file;
        }

        if (!isset($log['line'])) {
            $line = '';
            if (isset($log['extra']['line'])) {
                $line = $log['extra']['line'];
            }
            $log['line'] = $line;
        }

        if (!isset($log['message'])) {
            $log['message'] = '';
        }

        $log['record_time'] = date('Y-m-d H:i:s', $log['record_time']);
    }

    /**
     * 删除日志
     *
     * @param int $year 年
     * @param int $month 月
     * @param int $day 日
     */
    public function deleteLogs($year, $month = 0, $day = 0) {

        $dir = null;
        if ($month == 0) {
            $dir = Be::getRuntime()->getDataPath() . '/System/RuntimeLog/' . $year;
        } elseif ($day == 0) {
            $dir = Be::getRuntime()->getDataPath() . '/System/RuntimeLog/' . $year . '/' . $month;
        } else {
            $dir = Be::getRuntime()->getDataPath() . '/System/RuntimeLog/' . $year . '/' . $month . '/' . $day;
        }

        Be::getLib('Fso')->rmDir($dir);
    }
}
