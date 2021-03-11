<?php

namespace Be\Mf\Task;

/**
 * 计划任务
 */
class Task
{
    protected $task = null;
    protected $taskLog = null;

    public function __construct($task, $taskLog)
    {
        $this->task = $task;
        $this->taskLog = $taskLog;
    }


    public function execute()
    {

    }

}
