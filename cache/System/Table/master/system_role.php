<?php
namespace Be\Cache\System\Table\master;

class system_role extends \Be\System\Db\Table
{
    protected $_dbName = 'master'; // 数据库名
    protected $_tableName = 'system_role'; // 表名
    protected $_primaryKey = 'id'; // 主键
    protected $_fields = ['id','name','remark','permission','permissions','is_enable','is_delete','ordering','create_time','update_time']; // 字段列表
}
