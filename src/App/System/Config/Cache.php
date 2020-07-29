<?php

namespace Be\App\System\Config;

/**
 * @BeConfig("缓存")
 */
class Cache
{
    /**
     * @BeConfigItem("缓存类型", driver="\Be\\Plugin\Config\Item\ConfigItemSelect", values="['File','Redis']")
     */
    public $driver = 'File';

    /**
     * @BeConfigItem("REDIS设置项", driver="\Be\\Plugin\Config\Item\ConfigItemMixed")
     */
    public $redis = [
        'host' => '127.0.0.1', // 主机名
        'port' => 6379, // 端口号
        'timeout' => 10, // 超时时间
        'persistent' => false, // 是否使用长连接
        'password' => '', // 密码，不需要时留空
        'db' => 0 // 默认选中数据库
    ];

}
