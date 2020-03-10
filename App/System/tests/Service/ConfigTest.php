<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Be\System\Be;

class ConfigTest extends TestCase
{

    public function testGetConfigTree()
    {
        $service = Be::getService('System.Config');
        $result = $service->getConfigTree();
        var_dump($result);
        $this->assertTrue(is_array($result));
    }

    public function testGetConfig()
    {
        $service = Be::getService('System.Config');
        $result = $service->getConfig('System.User');
        print_r($result);
        $this->assertTrue(is_array($result));
    }

}
