<?php

namespace Be\App\System\Controller;

use Be\System\Be;
use Be\System\Request;
use Be\System\Response;
use Be\Util\Net\FileUpload;

/**
 * @BePermissionGroup("*")
 */
class Plugin extends \Be\System\Controller
{

    /**
     * @BePermission("*")
     */
    public function uploadFile()
    {
        $appName = Request::get('appName');
        $configName = Request::get('configName');
        $itemName = Request::get('itemName');

        $file = Request::files($itemName);
        if ($file['error'] == 0) {

            $service = Be::getService('System.Config');
            $config = $service->getConfig($appName, $configName);
            $configItemDriver = $config['items'][$itemName]['driver'];

            if ($file['size'] > $configItemDriver->maxSizeInt) {
                Response::set('success', false);
                Response::set('message', '您上传的图像尺寸已超过最大限制：' . $configItemDriver->maxSize . '！');
                Response::ajax();
            }

            $ext = '';
            $rPos = strrpos($file['name'], '.');
            if ($rPos !== false) {
                $ext = substr($file['name'], $rPos + 1);
            }
            if (!in_array($ext, $configItemDriver->allowUploadFileTypes)) {
                Response::error('禁止上传的文件类型：' . $ext . '！');
            }

            $newFileName = date('YmdHis') . '-' . \Be\Util\Random::simple(10) . '.' . $ext;
            $newFilePath = Be::getRuntime()->getDataPath() . $configItemDriver->path . $newFileName;
            if (move_uploaded_file($file['tmp_name'], $newFilePath)) {
                $newFileUrl = Be::getRuntime()->getDataUrl() . $configItemDriver->path . $newFileName;
                Response::set('newValue', $newFileName);
                Response::set('url', $newFileUrl);
                Response::set('success', true);
                Response::set('message', '上传成功！');
                Response::ajax();
            } else {
                Response::set('success', false);
                Response::set('message', '服务器处理上传文件出错！');
                Response::ajax();
            }
        } else {
            $errorDesc = FileUpload::errorDescription($file['error']);
            Response::set('success', false);
            Response::set('message', '上传失败' . '(' . $errorDesc . ')');
            Response::ajax();
        }
    }

    /**
     * @BePermission("*")
     */
    public function uploadImage()
    {
        $appName = Request::get('appName');
        $configName = Request::get('configName');
        $itemName = Request::get('itemName');

        $file = Request::files($itemName);
        if ($file['error'] == 0) {
            $service = Be::getService('System.Config');
            $config = $service->getConfig($appName, $configName);
            $configItemDriver = $config['items'][$itemName]['driver'];

            if ($file['size'] > $configItemDriver->maxSizeInt) {
                Response::set('success', false);
                Response::set('message', '您上传的图像尺寸已超过最大限制：' . $configItemDriver->maxSize . '！');
                Response::ajax();
            }

            $ext = '';
            $rPos = strrpos($file['name'], '.');
            if ($rPos !== false) {
                $ext = substr($file['name'], $rPos + 1);
            }
            if (!in_array($ext, $configItemDriver->allowUploadImageTypes)) {
                Response::error('禁止上传的图像类型：' . $ext . '！');
            }

            ini_set('memory_limit', '-1');
            $libImage = Be::getLib('Image');
            $libImage->open($file['tmp_name']);
            if ($libImage->isImage()) {
                if ($configItemDriver->maxWidth > 0 && $configItemDriver->maxHeight> 0) {
                    if ($libImage->getWidth() > $configItemDriver->maxWidth || $libImage->getheight() > $configItemDriver->maxHeight) {
                        $libImage->resize($configItemDriver->maxWidth, $configItemDriver->maxHeight, 'scale');
                    }
                }

                $newImageName = date('YmdHis') . '-' . \Be\Util\Random::simple(10) . '.' . $libImage->getType();
                $newImagePath = Be::getRuntime()->getDataPath() . $configItemDriver->path . $newImageName;
                if ($libImage->save($newImagePath)) {
                    $newImageUrl = Be::getRuntime()->getDataUrl() . $configItemDriver->path . $newImageName;
                    Response::set('newValue', $newImageName);
                    Response::set('url', $newImageUrl);
                    Response::set('success', true);
                    Response::set('message', '上传成功！');
                    Response::ajax();
                }
            } else {
                Response::set('success', false);
                Response::set('message', '您上传的不是有效的图像文件！');
                Response::ajax();
            }
        } else {
            $errorDesc = FileUpload::errorDescription($file['error']);
            Response::set('success', false);
            Response::set('message', '上传失败' . '(' . $errorDesc . ')');
            Response::ajax();
        }
    }

}