<?php

namespace Be\Mf\Runtime;


use Be\Mf\Be;
use Be\Mf\Task\TaskHelper;
use Swoole\Coroutine;

class Task
{

    /**
     * 定时计划任务调度
     *
     * @param $process
     */
    public static function process($process) {
        while (true) {
            // 每分钟执行一次
            $sec = (int)date('s', time());
            $sleep = 60 - $sec;
            if ($sleep > 0) {
                \Swoole\Coroutine::sleep($sleep);
            }

            $tasks = [];
            try {
                $db = Be::newDb();
                $sql = 'SELECT * FROM system_task WHERE is_enable = 1 AND is_delete = 0 AND schedule != \'\'';
                $tasks = $db->getObjects($sql);
            } catch (\Throwable $t) {
            }

            if (count($tasks) == 0) return;

            $server = Be::getRuntime()->getHttpServer()->getSwooleHttpServer();
            $t = time();
            foreach ($tasks as $task) {
                if (TaskHelper::isOnTime($task->schedule, $t)) {
                    $server->task($task);
                }
            }
        }
    }

    /**
     * \Swoole\Http\Server task 回调
     *
     * @param \Swoole\Http\Server $swooleHttpServer
     * @param int $taskId
     * @param int $reactorId
     * @param object $task
     */
    public static function onTask($swooleHttpServer, $taskId, $reactorId, $task)
    {
        $class = '\\Be\\Mf\\' . $task->app . '\\Task\\' . $task->name;
        if (class_exists($class)) {
            $db = Be::newDb();

            // 有任务正在运行
            $sql = 'SELECT COUNT(*) FROM system_task_log WHERE status = \'RUNNING\'';
            if ($db->getValue($sql) > 0) {
                return;
            }

            $taskLog = new \stdClass();
            try {
                $taskLog->task_id = $task->id;
                $taskLog->status = 'RUNNING';
                $taskLog->message = '';
                $taskLog->trigger = $task->trigger ?? 'SYSTEM';
                $taskLog->complete_time = '0000-00-00 00:00:00';
                $taskLog->create_time = date('Y-m-d H:i:s');
                $taskLog->update_time = date('Y-m-d H:i:s');
                $taskLogId = $db->insert('system_task_log', $taskLog);
                $taskLog->id = $taskLogId;

                $instance = new $class($task, $taskLog);
                $instance->execute();

                $now = date('Y-m-d H:i:s');
                $db = Be::newDb();
                $db->update('system_task_log', [
                    'id' => $taskLog->id,
                    'status' => 'COMPLETE',
                    'complete_time' => $now,
                    'update_time' => $now
                ]);

                $db->update('system_task', [
                    'id' => $task->id,
                    'last_execute_time' => $now,
                    'update_time' => $now
                ]);
                //返回任务执行的结果
                //$server->finish("{$data} -> OK");
            } catch (\Throwable $t) {
                if ($taskLog->id > 0) {
                    $now = date('Y-m-d H:i:s');
                    Be::newDb()->update('system_task_log', [
                        'id' => $taskLog->id,
                        'status' => 'ERROR',
                        'message' => $t->getMessage(),
                        'update_time' => $now
                    ]);
                }
            }
        }
    }


}
