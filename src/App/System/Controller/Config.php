<?php

namespace Be\App\System\Controller;

use Be\System\Be;
use Be\System\Request;
use Be\System\Response;
use Be\Util\Net\FileUpload;

/**
 * @BeMenuGroup("配置中心")
 * @BePermissionGroup("配置中心")
 */
class Config extends \Be\System\Controller
{

    /**
     * @BeMenu("配置中心")
     * @BePermission("查看")
     */
    public function dashboard()
    {
        $service = Be::getService('System.Config');
        $configTree = $service->getConfigTree();
        if (count($configTree) == 0) {
            Response::error('暂无配置项');
        }
        Response::set('configTree', $configTree);

        $appName = Request::get('appName', '');
        $configName = Request::get('configName', '');
        if (!$appName || !$configName) {
            $appName = $configTree[0]['configs'][0]['appName'];
            $configName = $configTree[0]['configs'][0]['configName'];
        }

        Response::set('appName', $appName);
        Response::set('configName', $configName);

        $config = $service->getConfig($appName, $configName);
        Response::set('config', $config);

        Response::setTitle('配置中心');
        Response::display();
    }

    /**
     * @BePermission("修改")
     */
    public function saveConfig()
    {
        try {
            Be::getService('System.Config')->saveConfig(Request::get('appName'), Request::get('configName'), Request::json());
            Response::success('保存成功！');
        } catch (\Exception $e) {
            Response::error('保存失败：' . $e->getMessage());
        }
    }

    /**
     * @BePermission("恢复默认值")
     */
    public function resetConfig()
    {
        try {
            Be::getService('System.Config')->resetConfig(Request::get('appName'), Request::get('configName'));
            Response::success('恢复默认值成功！');
        } catch (\Exception $e) {
            Response::error('恢复默认值失败：' . $e->getMessage());
        }
    }

    /**
     * @BePermission("修改")
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
     * @BePermission("修改")
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