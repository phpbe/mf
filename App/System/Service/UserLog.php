<?php
namespace Be\App\System\Service;

use Be\System\Be;
use Be\System\Service;

class UserLog extends Service
{

    /**
     * 删除三个月(90天)前的后台管理员登陆日志
     *
     * @throws \Exception
     */
    public function deleteLogs()
    {
        Be::newTable('system__user_log')->where('create_time', '<', (time() - 90 * 86400))->delete();
    }

}
