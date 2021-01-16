<?php
namespace Be\Mf\Cache\TableProperty\master;

class system_theme extends \Be\F\Db\TableProperty
{
    protected $_dbName = 'master'; // 数据库名
    protected $_tableName = 'system_theme'; // 表名
    protected $_primaryKey = 'id'; // 主键
    protected $_fields = array (
  'id' => 
  array (
    'name' => 'id',
    'type' => 'int',
    'length' => '11',
    'precision' => '',
    'scale' => '',
    'comment' => '自增ID',
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
    'comment' => '应用名',
    'default' => NULL,
    'nullAble' => false,
    'unsigned' => false,
    'collation' => 'utf8_general_ci',
    'key' => 'UNI',
    'extra' => '',
    'privileges' => 'select,insert,update,references',
    'autoIncrement' => 0,
    'isNumber' => 0,
  ),
  'label' => 
  array (
    'name' => 'label',
    'type' => 'varchar',
    'length' => '60',
    'precision' => '',
    'scale' => '',
    'comment' => '应用中文标识',
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
  'install_time' => 
  array (
    'name' => 'install_time',
    'type' => 'timestamp',
    'length' => '',
    'precision' => '',
    'scale' => '',
    'comment' => '安装时间',
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

