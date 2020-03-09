<?php
namespace Be\App\System\Service;

use Be\System\Be;
use Be\System\Request;

class AdminLog extends \Be\System\Service
{


    /**
     * 获取符合条件的后台日志列表
     *
     * @param array $conditions 查询条件
     * @return array
     */
    public function getLogs($conditions = [])
    {
        $tableAdminLog = Be::newTable('system_admin_log');

        $where = $this->createWhere($conditions);
        $tableAdminLog->where($where);

        if (isset($conditions['orderByString']) && $conditions['orderByString']) {
            $tableAdminLog->orderBy($conditions['orderByString']);
        } else {
            $orderBy = 'id';
            $orderByDir = 'DESC';
            if (isset($conditions['orderBy']) && $conditions['orderBy']) $orderBy = $conditions['orderBy'];
            if (isset($conditions['orderByDir']) && $conditions['orderByDir']) $orderByDir = $conditions['orderByDir'];
            $tableAdminLog->orderBy($orderBy, $orderByDir);
        }

        if (isset($conditions['offset']) && $conditions['offset']) $tableAdminLog->offset($conditions['offset']);
        if (isset($conditions['limit']) && $conditions['limit']) $tableAdminLog->limit($conditions['limit']);

        return $tableAdminLog->getObjects();
    }

    /**
     * 获取符合条件的后台日志总数
     *
     * @param array $conditions 查询条件
     * @return int
     */
    public function getLogCount($conditions = [])
    {
        return Be::newTable('system_admin_log')
            ->where($this->createWhere($conditions))
            ->count();
    }

    /**
     * 生成查询条件 where 数组
     *
     * @param array $conditions 查询条件
     * @return array
     */
    private function createWhere($conditions = [])
    {
        $where = [];

        if (isset($conditions['userId']) && is_numeric($conditions['userId'])) {
            $where[] = ['user_id', $conditions['userId']];
        }

        return $where;
    }



    public function addLog($content, $details = '')
    {
        $my = Be::getAdminUser();
        $tupleAdminLog = Be::newTuple('system_admin_log');
        $tupleAdminLog->user_id = $my->id;
        $tupleAdminLog->username = $my->username;
        $tupleAdminLog->name = $my->name;
        $tupleAdminLog->content = $content;
        $tupleAdminLog->details = json_encode($details);
        $tupleAdminLog->ip = Request::ip();
        $tupleAdminLog->create_time = time();
        $tupleAdminLog->save();
    }

    /**
     * 删除三个月(90天)前的后台用户登陆日志
     */
    public function deleteLogs()
    {
        Be::newTable('system_admin_log')->where('create_time', '<', (time() - 90 * 86400))->delete();
    }


}
