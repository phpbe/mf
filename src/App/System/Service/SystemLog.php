<?php
namespace Be\App\System\Service;

use Be\System\Be;
use Be\System\Exception\RuntimeException;
use Be\System\Request;

class SystemLog extends \Be\System\Service
{
    /**
     * @param $content
     * @param string $details
     * @throws RuntimeException
     */
    public function addLog($content, $details = '')
    {
        $my = Be::getUser();
        $tupleAdminLog = Be::newTuple('system_log');
        $tupleAdminLog->user_id = $my->id;
        $tupleAdminLog->content = $content;
        $tupleAdminLog->details = json_encode($details);
        $tupleAdminLog->ip = Request::ip();
        $tupleAdminLog->create_time = date('Y-m-d H:i:s');
        $tupleAdminLog->save();
    }

}
