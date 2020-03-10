<?php

namespace Be\App\System\AdminController;

use Be\System\Be;
use Be\System\Request;
use Be\System\Response;

/**
 * @be-menu-group 设置
 * @be-menu-group-icon tool
 * @be-permission-group 设置
 */
class Watermark extends \Be\System\AdminController
{


    /**
     * @be-menu 水印测试
     * @be-menu-icon picture
     * @be-permission 水印测试
     */
    public function test()
    {
        $src = Be::getRuntime()->getDataPath() . '/System/Watermark/test-0.jpg';
        $dst = Be::getRuntime()->getDataPath() . '/System/Watermark/test-1.jpg';

        if (!file_exists($src)) Response::end(Be::getRuntime()->getDataDir() . '/System/Watermark/test-0.jpg 文件不存在');
        if (file_exists($dst)) @unlink($dst);

        copy($src, $dst);

        sleep(1);

        $adminServiceSystem = Be::getService('System.Admin');
        $adminServiceSystem->watermark($dst);

        Response::setTitle('水印预览');
        Response::display();
    }

}