<?php
namespace Be\App\System;

use Be\System\Be;
use Be\Util\Random;

/**
 * 应用安装器
 */
class Installer extends \Be\System\App\Installer
{

    /**
     * 安装时需要执行的操作，如创建数据库表
     */
	public function install()
	{
        $db = Be::getDb();

        $sql = file_get_contents(__DIR__ . '/Installer.sql');
        $sqls = preg_split('/; *[\r\n]+/', $sql);
        foreach ($sqls as $sql) {
            $sql = trim($sql);
            if ($sql) {
                $db->query($sql);
            }
        }

        // 生成计划任务调度 KEY
        $config = Be::getConfig('System.Task');
        $config->key = Random::complex(16);
        Be::getService('System.Config')->save('System.Task', $config);
	}

}
