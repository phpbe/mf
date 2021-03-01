<?php

namespace Be\Mf\App\System\Controller;


use Be\Mf\Be;


/**
 * @BeMenuGroup("管理")
 * @BePermissionGroup("管理")
 */
class Task
{

    /**
     * @BeMenu("计划任务", icon="el-icon-timer")
     * @BePermission("计划任务")
     */
    public function dashboard()
    {
        Be::getPlugin('Task')->setting(['appName' => 'System'])->execute();
    }

    /**
     * 侓康检查
     */
    public function health()
    {

        $lastTaskLog = Be::newTable('system_task_log')
            ->orderBy('id', 'DESC')
            ->getObject();
    }

    /**
     * 执行计划任务调度
     */
    public function dispatch()
    {
        // 抽取任务
        $extractTasks = Be::newTable('system_task')
            ->where('is_enable', 1)
            ->where('schedule', '!=', '')
            ->getObjects();
        //print_r($extractTasks);

        $t = time();
        foreach ($extractTasks as $extractTask) {
            $url = beUrl('System.Task.run', ['id' => $extractTask->id, 't' => $t]);
            echo $url . '<br>';
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HEADER, 1);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_TIMEOUT, 1);
            curl_exec($curl);
            curl_close($curl);
        }

        echo '-';
    }


    /**
     * 执行计划任务调度
     */
    public function run()
    {

    }

}
