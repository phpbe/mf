<?php

namespace Be\App\System\Config;

/**
 * @be-config-label SESSION
 */
class Session
{

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemString
     * @be-config-item-label 名称
     * @be-config-item-description 用在 cookie 或者 URL 中的会话名称， 例如：PHPSESSID。 只能使用字母和数字，建议尽可能的短一些
     */
    public $name = 'SSID';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemInt
     * @be-config-item-label 超时时间
     * @be-config-item-ui {":min":1}
     */
    public $expire = 1440;

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemString
     * @be-config-item-label SESSION 驱动
     * @be-config-item-description SESSION 驱动 Default：系统默认/Mysql/Memcache/Memcached/Redis
     * @be-config-item-keyValues {"Default":"Default","Mysql":"Mysql","Memcache":"Memcache","Memcached":"Memcached","Redis":"Redis"}
     */
    public $driver = 'Default';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemMixed
     * @be-config-item-label REDIS设置项
     */
    public $redis = [
        'host' => '127.0.0.1', // 主机名
        'port' => 6379, // 端口号
        'timeout' => 0, // 超时时间
        'persistent' => false, // 是否使用长连接
        'password' => '', // 密码，不需要时留空
        'db' => 0 // 默认选中数据库
    ];

}