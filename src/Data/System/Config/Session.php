<?php

namespace Be\Mf\Data\System\Config;


class Session
{
    public $name = 'SSID';
    public $expire = 1440;
    public $host = '172.24.0.110';
    public $port = 6379;
    public $timeout = 5;
    public $auth = '';
    public $db = 0;
}
