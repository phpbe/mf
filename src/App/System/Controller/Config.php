<?php

namespace Be\App\System\Controller;

use Be\System\Be;
use Be\System\Request;
use Be\System\Response;

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
    public function uploadFile() {

        $file = Request::files('file');

        if ($file['error'] == 0) {

            $app = Request::get('_app');
            $config = Request::get('_config');
            $item = Request::get('_item');

            $service = Be::getService('System.Config');
            $configObj = $service->getConfig($app, $config);
            $configItemObj = null;
            foreach ($configObj['items'] as $x) {
                if ($x->name == $item) {
                    $configItemObj = $x;
                    break;
                }
            }

            if ($file['size'] > $configItemObj->option['maxSizeInt']) {
                Response::error('您上传的文件尺寸已超过最大限制：'.$configItemObj->option['maxSize'].'！');
            }


            $ext = '';
            $rpos = strrpos($file['name'], '.');
            if ($rpos !== false) {
                $ext = substr($file['name'], $rpos + 1);
            }

            if (!in_array($ext, $configItemObj->option['allowUploadFileTypes'])) {
                Response::error('禁止上传的文件类型：'.$ext.'！');
            }

            $newName = date('YmdHis') . '-' . \Be\Util\Random::simple(10) . '.' . $ext;
            $newPath = Be::getRuntime()->getDataPath() . $configItemObj->option['path'] . $newName;

            if (move_uploaded_file($file['tmp_name'], $newPath)) {
                $newUrl = Be::getRuntime()->getDataUrl() . $configItemObj->option['path'] . $newName;

                Response::set('newValue', $newName);
                Response::set('url', $newUrl);
                Response::success('上传成功！');

            } else {
                Response::error('服务器处理上传文件出错！');
            }

        } else {
            $uploadErrors = array(
                '1' => '上传的文件过大（超过了 php.ini 中 upload_max_filesize 选项限制的值：' . ini_get('upload_max_filesize') . '）！',
                '2' => '上传的文件过大（超过了 php.ini 中 post_max_size 选项限制的值：' . ini_get('post_max_size') . '）！',
                '3' => '文件只有部分被上传！',
                '4' => '没有文件被上传！',
                '5' => '上传的文件大小为 0！',
                '6' => '找不到临时文件夹！',
                '7' => '文件写入失败！'
            );
            $error = null;
            if (array_key_exists($file['error'], $uploadErrors)) {
                $error = $uploadErrors[$file['error']];
            } else {
                $error = '错误代码：' . $file['error'];
            }

            Response::error('上传失败' . '(' . $error . ')');
        }
    }

    /**
     * @BePermission("修改")
     */
    public function uploadImage() {

        $file = Request::files('file');
        if ($file['error'] == 0) {

            $app = Request::get('_app');
            $config = Request::get('_config');
            $item = Request::get('_item');

            $service = Be::getService('System.Config');
            $configObj = $service->getConfig($app, $config);
            $configItemObj = null;
            foreach ($configObj['items'] as $x) {
                if ($x->name == $item) {
                    $configItemObj = $x;
                    break;
                }
            }

            if ($file['size'] > $configItemObj->option['maxSizeInt']) {
                Response::error('您上传的图像尺寸已超过最大限制：'.$configItemObj->option['maxSize'].'！');
            }

            $libImage = Be::getLib('Image');
            $libImage->open($file['tmp_name']);
            if ($libImage->isImage()) {

                if ($configItemObj->option['maxWidth'] > 0 && $configItemObj->option['maxHeight'] > 0) {
                    if ($libImage->getWidth() > $configItemObj->option['maxWidth']|| $libImage->getheight() > $configItemObj->option['maxHeight']) {
                        $libImage->resize($configItemObj->option['maxWidth'], $configItemObj->option['maxHeight'], 'scale');
                    }
                }

                $newImageName = date('YmdHis') . '-' . \Be\Util\Random::simple(10) . '.' . $libImage->getType();
                $newImagePath = Be::getRuntime()->getDataPath() . $configItemObj->option['path'] . $newImageName;

                if ($libImage->save($newImagePath)) {

                    $newImageUrl = Be::getRuntime()->getDataUrl() . $configItemObj->option['path'] . $newImageName;

                    Response::set('newValue', $newImageName);
                    Response::set('url', $newImageUrl);
                    Response::success('上传成功！');
                }
            } else {
                Response::error('您上传的不是有效的图像文件！');
            }
        } else {
            $uploadErrors = array(
                '1' => '上传的文件过大（超过了 php.ini 中 upload_max_filesize 选项限制的值：' . ini_get('upload_max_filesize') . '）！',
                '2' => '上传的文件过大（超过了 php.ini 中 post_max_size 选项限制的值：' . ini_get('post_max_size') . '）！',
                '3' => '文件只有部分被上传！',
                '4' => '没有文件被上传！',
                '5' => '上传的文件大小为 0！',
                '6' => '找不到临时文件夹！',
                '7' => '文件写入失败！'
            );
            $error = null;
            if (array_key_exists($file['error'], $uploadErrors)) {
                $error = $uploadErrors[$file['error']];
            } else {
                $error = '错误代码：' . $file['error'];
            }

            Response::error('上传失败' . '(' . $error . ')');
        }
    }

}