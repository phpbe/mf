<?php

namespace Be\Mf\Task;

/**
 * 计划任务定时器
 */
class TaskInterval
{
    const HOURLY = -1;
    const DAILY = -2;
    const WEEKLY = -3;
    const MONTHLY = -4;
    const YEARLY = -5;

    // 断点
    protected $breakpoint = null;

    // 时间间隔
    protected $step = 600;

    // 每分钟执行一次
    protected $schedule = '* * * * *';


    public function __construct($data = [])
    {

    }


    public function execute()
    {

    }

}
