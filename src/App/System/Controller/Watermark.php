<?php

namespace Be\App\System\Controller;

use Be\System\Be;
use Be\System\Request;
use Be\System\Response;

/**
 * @BeMenuGroup("系统配置")
 * @BePermissionGroup("系统配置")
 */
class Watermark extends \Be\System\Controller
{

    /**
     * @BeMenu("水印测试", icon = "el-icon-fa fa-image", ordering="20.2")
     * @BePermission("水印测试", ordering="20.2")
     */
    public function test()
    {
        $src = Be::getRuntime()->getRootPath() . Be::getProperty('App.System')->getPath() . '/Template/Watermark/images/material.jpg';
        $dst = Be::getRuntime()->getDataPath() . '/System/Watermark/rendering.jpg';

        if (!file_exists($src)) Response::end($src . ' 不存在');
        if (file_exists($dst)) @unlink($dst);

        copy($src, $dst);

        sleep(1);

        $serviceWatermark = Be::getService('System.Watermark');
        $serviceWatermark->mark($dst);

        Response::setTitle('水印预览');
        Response::display();
    }

}