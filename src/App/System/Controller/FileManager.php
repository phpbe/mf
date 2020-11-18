<?php
namespace Be\App\System\Controller;

use Be\System\Be;
use Be\System\Request;
use Be\System\Response;
use Be\System\Session;

// 文件管理器
class FileManager
{

    public function browser()
    {
        // 要查看的路径
        $path = Request::post('path', '');

        // 显示方式 thumbnail 缩略图 list 详细列表
        $view = Request::post('view', '');

        // 排序
        $sort = Request::post('sort', '');

        // 只显示图像
        $filterImage = Request::get('filterImage', -1, 'int');

        $srcId = Request::get('srcId', '');


        // session 缓存用户选择
        if ($path == '') {
            $sessionPath = Session::get('systemFileManagerPath');
            if ($sessionPath != '') $path = $sessionPath;
        } else {
            if ($path == '/') $path = '';
            Session::set('systemFileManagerPath', $path);
        }

        if ($view == '') {
            $view = 'thumbnail';
            $sessionView = Session::get('systemFileManagerView');
            if ($sessionView != '' && ($sessionView == 'thumbnail' || $sessionView == 'list')) $view = $sessionView;
        } else {
            if ($view != 'thumbnail' && $view != 'list') $view = 'thumbnail';
            Session::set('systemFileManagerView', $view);
        }

        if ($sort == '') {
            $sessionSort = Session::get('systemFileManagerSort');
            if ($sessionSort == '') {
                $sort = 'name';
            } else {
                $sort = $sessionSort;
            }

        } else {
            Session::set('systemFileManagerSort', $sort);
        }

        if ($filterImage == -1) {
            $filterImage = 0;
            $sessionFilterImage = Session::get('systemFileManagerFilterImage', -1);
            if ($sessionFilterImage != -1 && ($sessionFilterImage == 0 || $sessionFilterImage == 1)) $filterImage = $sessionFilterImage;
        } else {
            if ($filterImage != 0 && $filterImage != 1) $filterImage = 0;
            Session::set('systemFileManagerFilterImage', $filterImage);
        }

        if ($srcId == '') {
            $srcId = Session::get('systemFileManagerSrcId', '');
        } elseif ($srcId == 'img') {
            $srcId = '';
            Session::set('systemFileManagerSrcId', $srcId);
        } else {
            Session::set('systemFileManagerSrcId', $srcId);
        }

        $option = array();
        $option['path'] = $path;
        $option['view'] = $view;
        $option['sort'] = $sort;
        $option['filterImage'] = $filterImage;

        $serviceSystemFileManager = Be::getService('System.FileManager');
        $files = $serviceSystemFileManager->getFiles($option);

        Response::set('path', $path);
        Response::set('view', $view);
        Response::set('sort', $sort);
        Response::set('filterImage', $filterImage);
        Response::set('srcId', $srcId);

        Response::set('files', $files);
        Response::display();
    }

    public function createDir()
    {
        $dirName = Request::post('dirName', '');
        $return = beUrl('System.FileManager.browser');

        $serviceSystemFileManager = Be::getService('System.FileManager');
        if ($serviceSystemFileManager->createDir($dirName)) {
            Response::success('创建文件夹(' . $dirName . ')成功！', $return);
        } else {
            Response::error($serviceSystemFileManager->getError(), $return);
        }
    }

    // 删除文件夹
    public function deleteDir()
    {
        $dirName = Request::get('dirName', '');
        $return = beUrl('System.FileManager.browser');

        $serviceSystemFileManager = Be::getService('System.FileManager');
        if ($serviceSystemFileManager->deleteDir($dirName)) {
            Response::success('删除文件夹(' . $dirName . ')成功！', $return);
        } else {
            Response::error($serviceSystemFileManager->getError(), $return);
        }
    }

    // 修改文件夹名称
    public function editDirName()
    {
        $oldDirName = Request::post('oldDirName', '');
        $newDirName = Request::post('newDirName', '');
        $return = beUrl('System.FileManager.browser');

        $serviceSystemFileManager = Be::getService('System.FileManager');
        if ($serviceSystemFileManager->editDirName($oldDirName, $newDirName)) {
            Response::success('重命名文件夹成功！', $return);
        } else {
            Response::error($serviceSystemFileManager->getError(), $return);
        }
    }


    public function uploadFile()
    {
        $configSystem = Be::getConfig('System.System');

        $return = beUrl('System.FileManager.browser');

        $file = $_FILES['file'];
        if ($file['error'] == 0) {
            $fileName = $file['name'];

            $type = strtolower(substr(strrchr($fileName, '.'), 1));
            if (!in_array($type, $configSystem->allowUploadFileTypes)) {
                Response::error('不允许上传(' . $type . ')格式的文件！', $return);
            }

            if (strpos($fileName, '/') !== false) {
                Response::error('文件名称不合法！', $return);
            }

            $serviceSystemFileManager = Be::getService('System.FileManager');
            $absPath = $serviceSystemFileManager->getAbsPath();
            if ($absPath == false) {
                Response::error($serviceSystemFileManager->getError(), $return);
            }

            $dstPath = $absPath . '/' . $fileName;

            $rename = false;
            if (file_exists($dstPath)) {
                $i = 1;
                $name = substr($fileName, 0, strrpos($fileName, '.'));
                while (file_exists($absPath . '/' . $name . '_' . $i . '.' . $type)) {
                    $i++;
                }

                $dstPath = $absPath . '/' . $name . '_' . $i . '.' . $type;

                $rename = $name . '_' . $i . '.' . $type;
            }

            if (move_uploaded_file($file['tmpName'], $dstPath)) {
                $watermark = Request::post('watermark', 0, 'int');
                if ($watermark == 1 && in_array($type, $configSystem->allowUploadImageTypes)) {
                    $serviceSystem = Be::getService('System.Admin');
                    $serviceSystem->watermark($dstPath);
                }

                if ($rename == false) {
                    Response::success('上传文件成功！', $return);
                } else {
                    Response::success('有同名文件，新上传的文件已更名为：' . $rename . '！', $return);
                }
            } else {
                Response::error('上传失败！', $return);
            }
        } else {

            $uploadErrors = array(
                '1' => '您上传的文件过大！',
                '2' => '您上传的文件过大！',
                '3' => '文件只有部分被上传！',
                '4' => '没有文件被上传！',
                '5' => '上传的文件大小为 0！'
            );

            $error = '';
            if (array_key_exists($file['error'], $uploadErrors)) {
                $error = $uploadErrors[$file['error']];
            } else {
                $error = '错误代码：' . $file['error'];
            }

            Response::error('上传失败' . '(' . $error . ')', $return);
        }
    }

    // 删除文件
    public function deleteFile()
    {
        $fileName = Request::get('fileName', '');
        $return = beUrl('System.FileManager.browser');

        $serviceSystemFileManager = Be::getService('System.FileManager');
        if ($serviceSystemFileManager->deleteFile($fileName)) {
            Response::success('删除文件(' . $fileName . ')成功！', $return);
        } else {
            Response::error($serviceSystemFileManager->getError(), $return);
        }
    }

    // 修改文件名称
    public function editFileName()
    {
        $oldFileName = Request::post('oldFileName', '');
        $newFileName = Request::post('newFileName', '');

        $serviceSystemFileManager = Be::getService('System.FileManager');
        if ($serviceSystemFileManager->editFileName($oldFileName, $newFileName)) {
            Response::success('重命名文件成功！', beUrl('System.FileManager.browser'));
        } else {
            Response::error($serviceSystemFileManager->getError(), beUrl('System.FileManager.browser'));
        }

    }

    public function downloadFile()
    {
        $fileName = Request::get('fileName', '');

        $serviceSystemFileManager = Be::getService('System.FileManager');
        $absFilePath = $serviceSystemFileManager->getAbsFilePath($fileName);
        if ($absFilePath == false) {
            echo $serviceSystemFileManager->getError();
        } else {
            header('Pragma: private');
            header('Cache-control: private, must-revalidate');
            header("Content-Type: application/octet-stream");
            header("Content-Length: " . (string)(filesize($absFilePath)));
            header('Content-Disposition: attachment; filename="' . ($fileName) . '"');
            readfile($absFilePath);
        }
        exit;
    }

}
