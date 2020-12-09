<?php

namespace Be\App\System\Controller;

use Be\System\Be;
use Be\System\Request;
use Be\System\Response;
use Be\Util\FileSystem\FileSize;
use Be\Util\Net\FileUpload;

/**
 * @BePermissionGroup("*")
 */
class Plugin
{

    public function uploadFile()
    {
        $file = Request::files('file');
        if ($file['error'] == 0) {

            $configSystem = Be::getConfig('System.System');
            $maxSize = $configSystem->uploadMaxSize;
            $maxSizeInt = FileSize::string2Int($maxSize);
            if ($file['size'] > $maxSizeInt) {
                Response::set('success', false);
                Response::set('message', '您上传的文件尺寸已超过最大限制：' . $maxSize . '！');
                Response::json();
            }

            $ext = '';
            $rPos = strrpos($file['name'], '.');
            if ($rPos !== false) {
                $ext = substr($file['name'], $rPos + 1);
            }
            if (!in_array($ext, $configSystem->allowUploadFileTypes)) {
                Response::set('success', false);
                Response::set('message', '禁止上传的文件类型：' . $ext . '！');
                Response::json();
            }

            $newFileName = date('YmdHis') . '-' . \Be\Util\Random::simple(10) . '.' . $ext;
            $newFilePath = Be::getRuntime()->getDataPath() . '/tmp/' . $newFileName;
            if (move_uploaded_file($file['tmp_name'], $newFilePath)) {
                $newFileUrl = Be::getRuntime()->getDataUrl(). '/tmp/' . $newFileName;
                Response::set('newValue', $newFileName);
                Response::set('url', $newFileUrl);
                Response::set('success', true);
                Response::set('message', '上传成功！');
                Response::json();
            } else {
                Response::set('success', false);
                Response::set('message', '服务器处理上传文件出错！');
                Response::json();
            }
        } else {
            $errorDesc = FileUpload::errorDescription($file['error']);
            Response::set('success', false);
            Response::set('message', '上传失败' . '(' . $errorDesc . ')');
            Response::json();
        }
    }

    public function uploadAvatar()
    {
        $file = Request::files('file');
        if ($file['error'] == 0) {

            $configSystem = Be::getConfig('System.System');
            $maxSize = $configSystem->uploadMaxSize;
            $maxSizeInt = FileSize::string2Int($maxSize);
            if ($file['size'] > $maxSizeInt) {
                Response::set('success', false);
                Response::set('message', '您上传的头像尺寸已超过最大限制：' . $maxSize . '！');
                Response::json();
            }

            $ext = '';
            $rPos = strrpos($file['name'], '.');
            if ($rPos !== false) {
                $ext = substr($file['name'], $rPos + 1);
            }
            if (!in_array($ext, $configSystem->allowUploadImageTypes)) {
                Response::set('success', false);
                Response::set('message', '禁止上传的图像类型：' . $ext . '！');
                Response::json();
            }

            ini_set('memory_limit', '-1');
            $libImage = Be::getLib('Image');
            $libImage->open($file['tmp_name']);
            if ($libImage->isImage()) {
                $maxWidth = Request::post('maxWidth', 0, 'int');
                $maxHeight = Request::post('maxHeight', 0, 'int');

                if ($maxWidth > 0 && $maxHeight> 0) {
                    if ($libImage->getWidth() > $maxWidth || $libImage->getheight() > $maxHeight) {
                        $libImage->resize($maxWidth, $maxHeight, 'center');
                    }
                }

                $newImageName = date('YmdHis') . '-' . \Be\Util\Random::simple(10) . '.' . $libImage->getType();
                $newImagePath = Be::getRuntime()->getDataPath() . '/tmp/' . $newImageName;

                if ($libImage->save($newImagePath)) {
                    $newImageUrl = Be::getRuntime()->getDataUrl(). '/tmp/' . $newImageName;
                    Response::set('newValue', $newImageName);
                    Response::set('url', $newImageUrl);
                    Response::set('success', true);
                    Response::set('message', '上传成功！');
                    Response::json();
                }
            } else {
                Response::set('success', false);
                Response::set('message', '您上传的不是有效的图像文件！');
                Response::json();
            }
        } else {
            $errorDesc = FileUpload::errorDescription($file['error']);
            Response::set('success', false);
            Response::set('message', '上传失败' . '(' . $errorDesc . ')');
            Response::json();
        }
    }

    public function uploadImage()
    {
        $file = Request::files('file');
        if ($file['error'] == 0) {

            $configSystem = Be::getConfig('System.System');
            $maxSize = $configSystem->uploadMaxSize;
            $maxSizeInt = FileSize::string2Int($maxSize);
            if ($file['size'] > $maxSizeInt) {
                Response::set('success', false);
                Response::set('message', '您上传的图像尺寸已超过最大限制：' . $maxSize . '！');
                Response::json();
            }

            $ext = '';
            $rPos = strrpos($file['name'], '.');
            if ($rPos !== false) {
                $ext = substr($file['name'], $rPos + 1);
            }
            if (!in_array($ext, $configSystem->allowUploadImageTypes)) {
                Response::set('success', false);
                Response::set('message', '禁止上传的图像类型：' . $ext . '！');
                Response::json();
            }

            ini_set('memory_limit', '-1');
            $libImage = Be::getLib('Image');
            $libImage->open($file['tmp_name']);
            if ($libImage->isImage()) {
                $maxWidth = Request::post('maxWidth', 0, 'int');
                $maxHeight = Request::post('maxHeight', 0, 'int');

                if ($maxWidth > 0 && $maxHeight> 0) {
                    if ($libImage->getWidth() > $maxWidth || $libImage->getheight() > $maxHeight) {
                        $libImage->resize($maxWidth, $maxHeight, 'scale');
                    }
                }

                $newImageName = date('YmdHis') . '-' . \Be\Util\Random::simple(10) . '.' . $libImage->getType();
                $newImagePath = Be::getRuntime()->getDataPath() . '/tmp/' . $newImageName;

                if ($libImage->save($newImagePath)) {
                    $newImageUrl = Be::getRuntime()->getDataUrl(). '/tmp/' . $newImageName;
                    Response::set('newValue', $newImageName);
                    Response::set('url', $newImageUrl);
                    Response::set('success', true);
                    Response::set('message', '上传成功！');
                    Response::json();
                }
            } else {
                Response::set('success', false);
                Response::set('message', '您上传的不是有效的图像文件！');
                Response::json();
            }
        } else {
            $errorDesc = FileUpload::errorDescription($file['error']);
            Response::set('success', false);
            Response::set('message', '上传失败' . '(' . $errorDesc . ')');
            Response::json();
        }
    }

}