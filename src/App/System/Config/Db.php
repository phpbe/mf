<?php
namespace Be\App\System\Config;

/**
 * @BeConfig("数据库")
 */
class Db
{

    /**
     * @BeConfigItem("主库", driver="\Be\\Plugin\Config\Item\ConfigItemMixed")
     */
    public $master = [
        'driver' => 'mysql',
        'host' => '127.0.0.1', // 主机名
        'port' => 3306, // 端口号
        'user' => 'root', // 用户名
        'pass' => 'root', // 密码
        'name' => 'be' // 数据库名称
    ]; // 主数据库

}
