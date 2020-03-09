<?php
namespace App\System\Config;

/**
 * @be-config-label 数据库
 */
class Db
{

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemMixed
     * @be-config-item-label 主库
     */
    public $master = [
        'driver' => 'mysql',
        'host' => '172.24.0.100', // 主机名
        'port' => 3306, // 端口号
        'user' => 'root', // 用户名
        'pass' => '12341234', // 密码
        'name' => 'phpbe' // 数据库名称
    ]; // 主数据库

}
