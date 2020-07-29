<?php

namespace Be\App\System\Config;

/**
 * @BeConfig("SESSION")
 */
class Session
{
    /**
     * @BeConfigItem("名称",
     *     driver="\Be\\Plugin\Config\Item\ConfigItemString",
     *     description = "用在 cookie 或者 URL 中的会话名称， 例如：PHPSESSID。 只能使用字母和数字，建议尽可能的短一些")
     */
    public $name = 'SSID';


    /**
     * @BeConfigItem("超时时间",
     *     driver="\Be\\Plugin\Config\Item\ConfigItemInt",
     *     ui = "['min' => 1]")
     */
    public $expire = 1440;

    /**
     * @BeConfigItem("SESSION 驱动",
     *     driver="\Be\\Plugin\Config\Item\ConfigItemInt",
     *     description = "SESSION 驱动 Default：系统默认/Redis"
     *     values = "['Default','Redis']")
     */
    public $driver = 'Default';

    /**
     * @BeConfigItem("REDIS设置项", driver="\Be\\Plugin\Config\Item\ConfigItemMixed")
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
