<?php

namespace Be\App\System\Controller;

use Be\System\Be;
use Be\System\Request;
use Be\System\Response;

class Install extends \Be\System\Controller
{

    public function __construct()
    {
        $config = Be::getConfig('System.System');
        if (!$config->developer) {
            Response::end('仅开发者模式下方可安装应用程序');
        }
    }

    /**
     * 安装应用
     *
     */
    public function app()
    {

    }

    /**
     * 安装应用
     *
     */
    public function theme()
    {

    }

}