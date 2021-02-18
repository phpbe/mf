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
    protected $server = null;

    public function execute()
    {
        $this->start();
    }

    public function start()
    {
        if ($this->server == null) {
            $this->server = new HttpServer();
            $this->server->start();
        }
    }

    public function reload()
    {
        if ($this->server !== null) {
            $this->server->reload();
        }
    }

    public function stop()
    {
        if ($this->server !== null) {
            $this->server->stop();
        }
    }

}
