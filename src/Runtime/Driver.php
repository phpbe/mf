<?php

namespace Be\Mf\Runtime;


/**
 *  MF框架运行时
 */
class Driver extends \Be\F\Runtime\Driver
{

    protected $frameworkName = 'Mf'; // 框架名称 Mf/Sf/Ff

    public function execute() {
        $httpServer = new HttpServer();
        $httpServer->start();
    }

}
