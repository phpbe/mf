<?php

namespace Be\Mf\Runtime;


/**
 *  MF框架运行时
 */
class Driver extends \Be\F\Runtime\Driver
{

    protected $frameworkName = 'Mf'; // 框架名称 Mf/Sf/Ff

    /**
     * @var HttpServer
     */
    protected $httpServer = null;

    public function execute()
    {
        if ($this->httpServer == null) {
            $this->httpServer = new HttpServer();
            $this->httpServer->start();
        }
    }

    public function getHttpServer() {
        return $this->httpServer;
    }

}
