<?php
namespace Be\Mf\App\System\Service;

use Be\Mf\Be;
use Be\F\Runtime\RuntimeException;

class OpLog
{
    /**
     * @param $content
     * @param string $details
     * @throws RuntimeException
     */
    public function addLog($content, $details = '')
    {
        $request = Be::getRequest();
        $my = Be::getUser();
        $tupleAdminLog = Be::newTuple('system_op_log');
        $tupleAdminLog->user_id = $my->id;
        $tupleAdminLog->app = $request->getAppName();
        $tupleAdminLog->controller = $request->getControllerName();
        $tupleAdminLog->action = $request->getActionName();
        $tupleAdminLog->content = $content;
        $tupleAdminLog->details = json_encode($details);
        $tupleAdminLog->ip = $request->getIp();
        $tupleAdminLog->create_time = date('Y-m-d H:i:s');
        $tupleAdminLog->save();
    }

}
