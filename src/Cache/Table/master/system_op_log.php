<?php
namespace Be\Mf\Cache\Table\master;

class system_op_log extends \Be\F\Db\Table
{
    protected $_dbName = 'master'; // 数据库名
    protected $_tableName = 'system_op_log'; // 表名
    protected $_primaryKey = 'id'; // 主键
    protected $_fields = ['id','user_id','app','controller','action','content','details','ip','create_time']; // 字段列表
}

