<?php
namespace Be\Cache\System\TableProperty\master;

class system_role extends \Be\System\Db\TableProperty
{
    protected $_dbName = 'master'; // 数据库名
    protected $_tableName = 'system_role'; // 表名
    protected $_primaryKey = 'id'; // 主键
    protected $_fields = array (
  'id' => 
  array (
    'name' => 'id',
    'type' => 'int',
    'length' => '11',
    'precision' => '',
    'scale' => '',
    'comment' => '自增编号',
    'default' => NULL,
    'nullAble' => false,
    'unsigned' => false,
    'collation' => NULL,
    'key' => 'PRI',
    'extra' => 'auto_increment',
    'privileges' => 'select,insert,update,references',
    'autoIncrement' => 1,
    'isNumber' => 1,
  ),
  'name' => 
  array (
    'name' => 'name',
    'type' => 'varchar',
    'length' => '60',
    'precision' => '',
    'scale' => '',
    'comment' => '角色名',
    'default' => '',
    'nullAble' => false,
    'unsigned' => false,
    'collation' => 'utf8_general_ci',
    'key' => '',
    'extra' => '',
    'privileges' => 'select,insert,update,references',
    'autoIncrement' => 0,
    'isNumber' => 0,
  ),
  'remark' => 
  array (
    'name' => 'remark',
    'type' => 'varchar',
    'length' => '200',
    'precision' => '',
    'scale' => '',
    'comment' => '备注',
    'default' => '',
    'nullAble' => false,
    'unsigned' => false,
    'collation' => 'utf8_general_ci',
    'key' => '',
    'extra' => '',
    'privileges' => 'select,insert,update,references',
    'autoIncrement' => 0,
    'isNumber' => 0,
  ),
  'permission' => 
  array (
    'name' => 'permission',
    'type' => 'tinyint',
    'length' => '4',
    'precision' => '',
    'scale' => '',
    'comment' => '权限',
    'default' => NULL,
    'nullAble' => false,
    'unsigned' => false,
    'collation' => NULL,
    'key' => '',
    'extra' => '',
    'privileges' => 'select,insert,update,references',
    'autoIncrement' => 0,
    'isNumber' => 1,
  ),
  'permissions' => 
  array (
    'name' => 'permissions',
    'type' => 'text',
    'length' => '',
    'precision' => '',
    'scale' => '',
    'comment' => '权限明细',
    'default' => NULL,
    'nullAble' => false,
    'unsigned' => false,
    'collation' => 'utf8_general_ci',
    'key' => '',
    'extra' => '',
    'privileges' => 'select,insert,update,references',
    'autoIncrement' => 0,
    'isNumber' => 0,
  ),
  'is_enable' => 
  array (
    'name' => 'is_enable',
    'type' => 'tinyint',
    'length' => '3',
    'precision' => '',
    'scale' => '',
    'comment' => '是否可用',
    'default' => '1',
    'nullAble' => false,
    'unsigned' => true,
    'collation' => NULL,
    'key' => '',
    'extra' => '',
    'privileges' => 'select,insert,update,references',
    'autoIncrement' => 0,
    'isNumber' => 1,
  ),
  'is_delete' => 
  array (
    'name' => 'is_delete',
    'type' => 'tinyint',
    'length' => '3',
    'precision' => '',
    'scale' => '',
    'comment' => '是否已删除',
    'default' => '0',
    'nullAble' => false,
    'unsigned' => true,
    'collation' => NULL,
    'key' => '',
    'extra' => '',
    'privileges' => 'select,insert,update,references',
    'autoIncrement' => 0,
    'isNumber' => 1,
  ),
  'ordering' => 
  array (
    'name' => 'ordering',
    'type' => 'int',
    'length' => '11',
    'precision' => '',
    'scale' => '',
    'comment' => '排序（越小越靠前）',
    'default' => NULL,
    'nullAble' => false,
    'unsigned' => false,
    'collation' => NULL,
    'key' => '',
    'extra' => '',
    'privileges' => 'select,insert,update,references',
    'autoIncrement' => 0,
    'isNumber' => 1,
  ),
  'create_time' => 
  array (
    'name' => 'create_time',
    'type' => 'timestamp',
    'length' => '',
    'precision' => '',
    'scale' => '',
    'comment' => '创建时间',
    'default' => 'CURRENT_TIMESTAMP',
    'nullAble' => false,
    'unsigned' => false,
    'collation' => NULL,
    'key' => '',
    'extra' => '',
    'privileges' => 'select,insert,update,references',
    'autoIncrement' => 0,
    'isNumber' => 0,
  ),
  'update_time' => 
  array (
    'name' => 'update_time',
    'type' => 'timestamp',
    'length' => '',
    'precision' => '',
    'scale' => '',
    'comment' => '更新时间',
    'default' => 'CURRENT_TIMESTAMP',
    'nullAble' => false,
    'unsigned' => false,
    'collation' => NULL,
    'key' => '',
    'extra' => 'on update CURRENT_TIMESTAMP',
    'privileges' => 'select,insert,update,references',
    'autoIncrement' => 0,
    'isNumber' => 0,
  ),
); // 字段列表
}
