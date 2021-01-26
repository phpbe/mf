<?php
namespace Be\Mf\App\System;

use Be\Mf\Be;
use Be\F\Util\Random;

/**
 * 应用安装器
 */
class Installer extends \Be\F\App\Installer
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
	}

}
