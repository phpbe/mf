<?php

namespace Be\Mf\App\System\Service;

use Be\Mf\Be;

class Task
{

    /**
     * 触发启动指定的计划任务
     *
     * @param string $task
     */
    public function trigger($task)
    {
        $parts = explode('.', $task);
        $app = $parts[0];
        $name = $parts[1];
        try {
            $tuple = Be::getTuple('system_task');
            $tuple->loadBy([
                'app' => $app,
                'name' => $name,
            ]);
            $tuple->trigger = 'RELATED';

            Be::getRuntime()->getHttpServer()->getSwooleHttpServer()->task($tuple->toObject());

            beOpLog('触发任务：' . $tuple->label . '（' . $tuple->app . '.' . $tuple->name . '）成功');
        } catch (\Throwable $t) {
            beOpLog('触发任务：' . $task . '）失败：' . $t->getMessage());
        }
    }

}
