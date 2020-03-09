<?php

namespace Be\App\System\Config;

/**
 * @be-config-label 缓存
 */
class Cache
{
    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemString
     * @be-config-item-label 缓存类型
     * @be-config-item-keyValues {"File":"File","Memcache":"Memcache","Memcached":"Memcached","Redis":"Redis"}
     */
    public $driver = 'File';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemMixed
     * @be-config-item-label Memcache设置项
     */
    public $memcache = [
        [
            'host' => '127.0.0.1', // 主机名
            'port' => 11211, // 端口号
            'timeout' => 0, // 超时时间
            'persistent' => false, // 是否使用长连接
            'weight' => 1 // 权重
        ]
    ];

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemMixed
     * @be-config-item-label Memcached设置项
     */
    public $memcached = [
        [
            'host' => '127.0.0.1', // 主机名
            'port' => 11211, // 端口号
            'weight' => 1 // 权重
        ]
    ];

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemMixed
     * @be-config-item-label REDIS设置项
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
