<?php
namespace Be\App\System\Controller;

use Be\System\Be;
use Be\System\Request;
use Be\System\Response;

/**
 * @be-menu-group 缓存管理
 * @be-permission-group 缓存管理
 */
class Cache extends \Be\System\Controller
{


    /**
     * @be-menu 缓存管理
     * @be-permission 缓存管理
     */
    public function cache()
    {
        Response::setTitle('缓存管理');
        Response::display();
    }


    /**
     * @be-menu 删除缓存
     * @be-permission 删除缓存
     */
    public function clearCache()
    {
        try {
            $type = Request::request('type');
            $serviceSystemCache = Be::getService('System.Cache');
            $serviceSystemCache->clear($type);
            systemLog('删除缓存（' . $type . '）');
            Response::success('删除缓存成功！', url('System.System.cache'));
        } catch (\Exception $e) {
            Response::error($e->getMessage());
        }
    }


}