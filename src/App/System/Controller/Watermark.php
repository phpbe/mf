<?php

namespace Be\Mf\App\System\Controller;

use Be\Mf\Be;
use Be\F\Request;
use Be\F\Response;

/**
 * @BeMenuGroup("系统配置")
 * @BePermissionGroup("系统配置")
 */
class Watermark
{

    /**
     * @BeMenu("水印测试", icon = "el-icon-fa fa-image", ordering="20.2")
     * @BePermission("水印测试", ordering="20.2")
     */
    public function test()
    {
        $response = Be::getResponse();

        $src = Be::getRuntime()->rootPath() . Be::getProperty('App.System')->path() . '/Template/Watermark/images/material.jpg';
        $dst = Be::getRuntime()->dataPath() . '/System/Watermark/rendering.jpg';

        if (!file_exists($src)) $response->end($src . ' 不存在');
        if (file_exists($dst)) @unlink($dst);

        copy($src, $dst);

        sleep(1);

        $serviceWatermark = Be::getService('System.Watermark');
        $serviceWatermark->mark($dst);

        $response->set('title', '水印预览');
        $response->display();
    }

}