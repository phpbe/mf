<?php
namespace Be\App\System\Controller;

use Be\System\Be;
use Be\System\Request;
use Be\System\Response;


/**
 * @BeMenuGroup("缓存管理", icon = "el-icon-fa fa-database")
 * @BePermissionGroup("缓存管理")
 */
class Cache
{


    /**
     * @BeMenu("缓存管理")
     * @BePermission("缓存管理")
     */
    public function cache()
    {
        Response::setTitle('缓存管理');
        Response::display();
    }

    /**
     * @BeMenu("删除缓存")
     * @BePermission("删除缓存")
     */
    public function clearCache()
    {
        try {
            $type = Request::request('type');
            $serviceSystemCache = Be::getService('System.Cache');
            $serviceSystemCache->clear($type);
            beSystemLog('删除缓存（' . $type . '）');
            Response::success('删除缓存成功！', beUrl('System.System.cache'));
        } catch (\Exception $e) {
            Response::error($e->getMessage());
        }
    }


}