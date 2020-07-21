<?php

namespace Be\App\System\Controller;

use Be\System\Be;
use Be\System\Request;
use Be\System\Response;

/**
 * @BeMenuGroup("设置")
 * @BePermissionGroup("设置")
 */
class Watermark extends \Be\System\Controller
{


    /**
     * @BeMenu("水印测试", icon = "el-icon-fa fa-image")
     * @BePermission("水印测试")
     */
    public function test()
    {
        $src = Be::getRuntime()->getRootPath() . Be::getProperty('App.System')->getUrl() . 'Template/System/Watermark/images/material.jpg';
        $dst = Be::getRuntime()->getDataPath() . '/System/Watermark/rendering.jpg';

        if (!file_exists($src)) Response::end(Be::getProperty('App.System')->getUrl() . '/Template/System/Watermark/images/material.jpg 文件不存在');
        if (file_exists($dst)) @unlink($dst);

        copy($src, $dst);

        sleep(1);

        $serviceWatermark = Be::getService('System.Watermark');
        $serviceWatermark->mark($dst);

        Response::setTitle('水印预览');
        Response::display();
    }

}