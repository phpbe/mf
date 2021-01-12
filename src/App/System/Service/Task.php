<?php

namespace Be\Mf\App\System\Service;

use Be\Mf\Be;
use Be\F\Exception\ServiceException;
use PHPMailer\PHPMailer\PHPMailer;

class Task
{


    /**
     * 新建计划任务
     *
     * @param string $driver 驱动
     * @param string $name 名称
     * @param string $schedule 执行计划
     */
    public function create($driver, $name, $schedule = '* * * * *')
    {
        $tuple = Be::getTuple('system_task');
        $tuple->driver = $driver;
        $tuple->name = $name;
        $tuple->schedule = $schedule;
        $tuple->insert();
    }

    /**
     * 替换计划任务，不存在时新建，按驱动($driver)值唯一
     *
     * @param string $driver 驱动
     * @param string $name 名称
     * @param string $schedule 执行计划
     */
    public function replace($driver, $name, $schedule = '* * * * *')
    {
        $tuple = Be::getTuple('system_task');
        try {
            $tuple->loadBy('driver', $driver);
        } catch (\Exception $e) {
            $tuple->driver = $driver;
        }

        $tuple->name = $name;
        $tuple->schedule = $schedule;
        $tuple->save();
    }

    /**
     * 是否有效的执行计划表达式
     * @param $schedule
     * @return bool
     */
    public function isScheduleValid($schedule) {
        $parts = explode(' ', trim($schedule));
        if (count($parts) != 5) {
            return false;
        }

        // 分
        if (is_numeric($parts[0])) {
            if ($parts[0] > 59 || $parts[0] < 0) {
                return false;
            }
        }

        // 时
        if (is_numeric($parts[1])) {
            if ($parts[1] > 23 || $parts[1] < 0) {
                return false;
            }
        }

        // 日
        if (is_numeric($parts[2])) {
            if ($parts[2] > 31 || $parts[2] < 1) {
                return false;
            }
        }

        // 月
        if (is_numeric($parts[3])) {
            if ($parts[3] > 12 || $parts[3] < 1) {
                return false;
            }
        }

        // 周
        if (is_numeric($parts[4])) {
            if ($parts[4] > 6 || $parts[4] < 0) {
                return false;
            }
        }

        foreach ($parts as $part) {
            if (is_numeric($part)) {
                if ($part != intval($part)) {
                    return false;
                }

                continue;
            }

            if ($part == '*') {
                continue;
            }

            $rules = explode(',', $part);
            foreach ($rules as $rule) {

                if (is_numeric($rule)) {
                    continue;
                }

                // 0-29/3
                if (strpos($rule, '/')) {
                    $fraction = explode('/', $rule);
                    if (count($fraction) != 2) {
                        return false;
                    }

                    $numerator = $fraction[0];
                    $denominator = $fraction[1];

                    if (!is_numeric($denominator)) {
                        return false;
                    }

                    if (strpos($numerator, '-')) {
                        $scheduleRuleValues = explode('-', $numerator);
                        if (count($scheduleRuleValues) != 2) {
                            return false;
                        }

                        if (!is_numeric($scheduleRuleValues[0]) || !is_numeric($scheduleRuleValues[1])) {
                            return false;
                        }

                    } else {
                        if ($numerator != '*') {
                            return false;
                        }
                    }
                } elseif (strpos($rule, '-')) {
                    // 30-59
                    $ruleValues = explode('-', $rule);
                    if (count($ruleValues) != 2) {
                        return false;
                    }

                    if (!is_numeric($ruleValues[0]) || !is_numeric($ruleValues[1])) {
                        return false;
                    }
                } else {
                    return false;
                }

            }

        }

        return true;
    }

    /**
     * 执行计划是否匹配对应时间
     *
     * @param string $schedule 执行计划，如: 0-29/2,30-59/3 1-2,4 1,3,5,7,9 1-6 *
     * @param int $timestamp 指定时间戳
     * @return bool
     */
    public function isOnTime($schedule, $timestamp = 0)
    {
        $schedule = explode(' ', $schedule);
        if (count($schedule) != 5) return false;

        if ($timestamp == 0) $timestamp = time();

        return $this->isScheduleMatch($schedule[0], date('i', $timestamp)) &&
            $this->isScheduleMatch($schedule[1], date('G', $timestamp)) &&
            $this->isScheduleMatch($schedule[2], date('j', $timestamp)) &&
            $this->isScheduleMatch($schedule[3], date('n', $timestamp)) &&
            $this->isScheduleMatch($schedule[4], date('N', $timestamp));
    }

    /**
     * 比对计划任务时间配置项是否匹配当前时间
     *
     * @param string $scheduleRule 计划任务时间配置项规则
     * @param int $timeValue 时间值
     * @return bool
     */
    protected function isScheduleMatch($scheduleRule, $timeValue)
    {
        if (!is_numeric($timeValue)) return false;
        $timeValue = intval($timeValue);

        $match = false;
        if ($scheduleRule == '*') {
            $match = true;
        } else {
            $scheduleRules = explode(',', $scheduleRule);
            foreach ($scheduleRules as $scheduleRule) {
                // 0-29/3
                if (strpos($scheduleRule, '/')) {
                    $fraction = explode('/', $scheduleRule);
                    if (count($fraction) != 2) {
                        continue;
                    }

                    $numerator = $fraction[0];
                    $denominator = $fraction[1];

                    if (!is_numeric($denominator)) {
                        continue;
                    }

                    if (strpos($numerator, '-')) {

                        $scheduleRuleValues = explode('-', $numerator);
                        if (count($scheduleRuleValues) != 2) {
                            continue;
                        }

                        if (!is_numeric($scheduleRuleValues[0]) || !is_numeric($scheduleRuleValues[1])) {
                            continue;
                        }

                        if ($scheduleRuleValues[0] <= $timeValue && $timeValue <= $scheduleRuleValues[1]) {
                            if (($timeValue - $scheduleRuleValues[0]) % $denominator == 0) {
                                $match = true;
                                break;
                            }
                        }

                    } else {
                        if ($numerator == '*') {
                            if ($timeValue % $denominator == 0) {
                                $match = true;
                                break;
                            }
                        }
                    }
                } else {
                    // 30-59
                    if (strpos($scheduleRule, '-')) {
                        $scheduleRuleValues = explode('-', $scheduleRule);
                        if (count($scheduleRuleValues) != 2) {
                            continue;
                        }

                        if (!is_numeric($scheduleRuleValues[0]) || !is_numeric($scheduleRuleValues[1])) {
                            continue;
                        }

                        if ($scheduleRuleValues[0] <= $timeValue && $timeValue <= $scheduleRuleValues[1]) {
                            $match = true;
                            break;
                        }
                    } else {
                        if ($scheduleRule == '*' || $scheduleRule == $timeValue) {
                            $match = true;
                            break;
                        }
                    }
                }
            }
        }

        return $match;
    }

}
