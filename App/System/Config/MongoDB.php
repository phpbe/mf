<?php
namespace Be\App\System\Config;

/**
 * @be-config-label MongoDB数据库配置
 */
class MongoDB
{

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemMixed
     * @be-config-item-label 主库
     */
    public $master = [
        'host' => '172.24.0.120', // 主机名
        'port' => 27017, // 端口号
        'db' => '' // 数据库
    ];

}
