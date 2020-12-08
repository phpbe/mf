<?php
namespace Be\App\System\Service;

use Be\System\Be;
use Be\System\Exception\RuntimeException;
use Be\System\Request;

class OpLog
{
    /**
     * @param $content
     * @param string $details
     * @throws RuntimeException
     */
    public function addLog($content, $details = '')
    {
        $runtime = Be::getRuntime();
        $my = Be::getUser();
        $tupleAdminLog = Be::newTuple('system_op_log');
        $tupleAdminLog->user_id = $my->id;
        $tupleAdminLog->app = $runtime->getAppName();
        $tupleAdminLog->controller = $runtime->getControllerName();
        $tupleAdminLog->action = $runtime->getActionName();
        $tupleAdminLog->content = $content;
        $tupleAdminLog->details = json_encode($details);
        $tupleAdminLog->ip = Request::ip();
        $tupleAdminLog->create_time = date('Y-m-d H:i:s');
        $tupleAdminLog->save();
    }

}
