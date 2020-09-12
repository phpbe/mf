<?php

namespace Be\System\Db\Driver;

use Be\System\Db\Driver;
use Be\System\Exception\DbException;

/**
 * 数据库类 MSSQL(SQL Server)
 */
class MssqlImpl extends Driver
{

    /**
     * 连接数据库
     *
     * @return \PDO 连接
     * @throws DbException
     */
    public function connect()
    {
        if ($this->connection === null) {
            $config = $this->config;

            $options = array(
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            );

            if (isset($config['options'])) {
                $options = $config['options'] + $options;
            }

            $dsn = null;
            if (isset($config['dsn']) && $config['dsn']) {
                $dsn = $config['dsn'];
            } else {
                $dsn = 'sqlsrv:Database=' . $config['name'] . ';Server=' . $config['host'];
                if (isset($config['port'])) {
                    $dsn .= ',' . $config['port'];
                }
            }

            $connection = new \PDO($dsn, $config['user'], $config['pass'], $options);
            if (!$connection) throw new DbException('连接MSSQL数据库' . $config['name'] . '（' . $config['host'] . '） 失败！');

            $this->connection = $connection;
        }

        return $this->connection;
    }

    /**
     * 插入一个对象到数据库
     *
     * @param string $table 表名
     * @param array | object $object 要插入数据库的对象或对象数组，对象属性需要和该表字段一致
     * @return int 插入的主键ID
     * @throws DbException
     */
    public function insert($table, $object)
    {
        $vars = null;
        if (is_array($object)) {
            $vars = $object;
        } elseif (is_object($object)) {
            $vars = get_object_vars($object);
        } else {
            throw new DbException('插入的数据格式须为对象或数组');
        }

        $fields = [];
        foreach (array_keys($vars) as $field) {
            $fields[] = $this->quoteKey($field);
        }

        $sql = 'INSERT INTO ' . $this->quoteKey($table) . '(' . implode(',', $fields) . ') VALUES(' . implode(',', array_fill(0, count($vars), '?')) . ')';
        $statement = $this->execute($sql, array_values($vars));
        $statement->closeCursor();
        return $this->getLastInsertId();
    }

    /**
     * 批量插入多个对象到数据库
     *
     * @param string $table 表名
     * @param array $objects 要插入数据库的对象数组，对象属性需要和该表字段一致
     * @return array 批量插入的ID列表
     * @throws DbException
     */
    public function insertMany($table, $objects)
    {
        if (!is_array($objects) || count($objects) == 0) return [];

        $ids = [];
        reset($objects);
        $object = current($objects);
        $vars = null;
        if (is_array($object)) {
            $vars = $object;
        } elseif (is_object($object)) {
            $vars = get_object_vars($object);
        } else {
            throw new DbException('批量插入的数据格式须为对象或数组');
        }
        ksort($vars);

        $fields = [];
        foreach (array_keys($vars) as $field) {
            $fields[] = $this->quoteKey($field);
        }

        $sql = 'INSERT INTO ' . $this->quoteKey($table) . '(' . implode(',', $fields) . ') VALUES(' . implode(',', array_fill(0, count($vars), '?')) . ')';
        $statement = $this->prepare($sql);
        foreach ($objects as $o) {
            $vars = null;
            if (is_array($o)) {
                $vars = $o;
            } elseif (is_object($o)) {
                $vars = get_object_vars($o);
            } else {
                throw new DbException('批量插入的数据格式须为对象或数组');
            }
            ksort($vars);
            $statement->execute(array_values($vars));
            $ids[] = $this->getLastInsertId();
        }
        $statement->closeCursor();

        return $ids;
    }

    /**
     * 快速插入一个对象到数据库
     *
     * @param string $table 表名
     * @param array | object $object 要插入数据库的对象，对象属性需要和该表字段一致
     * @return int 插入的主键ID
     * @throws DbException
     */
    public function quickInsert($table, $object)
    {
        $effectLines = null;

        $vars = null;
        if (is_array($object)) {
            $vars = $object;
        } elseif (is_object($object)) {
            $vars = get_object_vars($object);
        } else {
            throw new DbException('快速插入的数据格式须为对象或数组');
        }

        $fields = [];
        foreach (array_keys($vars) as $field) {
            $fields[] = $this->quoteKey($field);
        }

        $sql = 'INSERT INTO ' . $this->quoteKey($table) . '(' . implode(',', $fields) . ') VALUES';
        $values = array_values($vars);
        foreach ($values as &$value) {
            if ($value !== null) {
                $value = $this->quoteValue($value);
            } else {
                $value = 'null';
            }
        }
        $sql .= '(' . implode(',', $values) . ')';
        $statement = $this->execute($sql);
        $statement->closeCursor();

        return $this->getLastInsertId();
    }

    /**
     * 快速批量插入多个对象到数据库
     *
     * @param string $table 表名
     * @param array $objects 要插入数据库的对象数组，对象属性需要和该表字段一致
     * @return int 影响的行数
     * @throws DbException
     */
    public function quickInsertMany($table, $objects)
    {
        if (!is_array($objects) || count($objects) == 0) return 0;

        reset($objects);
        $object = current($objects);
        $vars = null;
        if (is_array($object)) {
            $vars = $object;
        } elseif (is_object($object)) {
            $vars = get_object_vars($object);
        } else {
            throw new DbException('快速批量插入的数据格式须为对象或数组');
        }
        ksort($vars);


        $fields = [];
        foreach (array_keys($vars) as $field) {
            $fields[] = $this->quoteKey($field);
        }

        $sql = 'INSERT INTO ' . $this->quoteKey($table) . '(' . implode(',', $fields) . ') VALUES';
        foreach ($objects as $o) {
            $vars = null;
            if (is_array($o)) {
                $vars = $o;
            } elseif (is_object($o)) {
                $vars = get_object_vars($o);
            } else {
                throw new DbException('快速批量插入的数据格式须为对象或数组');
            }
            ksort($vars);
            $values = array_values($vars);
            foreach ($values as &$value) {
                if ($value !== null) {
                    $value = $this->quoteValue($value);
                } else {
                    $value = 'null';
                }
            }
            $sql .= '(' . implode(',', $values) . '),';
        }
        $sql = substr($sql, 0, -1);
        $statement = $this->execute($sql);
        $effectLines = $statement->rowCount();
        $statement->closeCursor();

        return $effectLines;
    }


    /**
     * 更新一个对象到数据库
     *
     * @param string $table 表名
     * @param array | object $object 要插入数据库的对象，对象属性需要和该表字段一致
     * @param null | string | array $primaryKey 主键
     * @return int 影响的行数
     * @throws DbException
     */
    public function update($table, $object, $primaryKey = null)
    {
        $fields = [];
        $fieldValues = [];

        $where = [];
        $whereValue = [];

        if ($primaryKey === null) {
            $primaryKey = $this->getTablePrimaryKey($table);
            if ($primaryKey === null) {
                throw new DbException('新数据表' . $table . '无主键，不支持按主键更新！');
            }
        }

        $vars = null;
        if (is_array($object)) {
            $vars = $object;
        } elseif (is_object($object)) {
            $vars = get_object_vars($object);
        } else {
            throw new DbException('更新的数据格式须为对象或数组');
        }

        foreach ($vars as $key => $value) {
            if (is_array($value) || is_object($value)) {
                continue;
            }

            if (is_array($primaryKey)) {

                if (in_array($key, $primaryKey)) {
                    $where[] = $this->quoteKey($key) . '=?';
                    $whereValue[] = $value;
                    continue;
                }

            } else {

                // 主键不更新
                if ($key == $primaryKey) {
                    $where[] = $this->quoteKey($key) . '=?';
                    $whereValue[] = $value;
                    continue;
                }
            }

            $fields[] = $this->quoteKey($key) . '=?';
            $fieldValues[] = $value;
        }

        if (!$where) {
            throw new DbException('更新数据时未指定条件！');
        }

        $sql = 'UPDATE ' . $this->quoteKey($table) . ' SET ' . implode(',', $fields) . ' WHERE ' . implode(' AND ', $where);
        $fieldValues = array_merge($fieldValues, $whereValue);

        $statement = $this->execute($sql, $fieldValues);
        $effectLines = $statement->rowCount();
        $statement->closeCursor();

        return $effectLines;
    }

    /**
     * 快速更新一个对象到数据库
     *
     * @param string $table 表名
     * @param array | object $object 要插入数据库的对象，对象属性需要和该表字段一致
     * @param null | string | array $primaryKey 主键
     * @return int 影响的行数
     * @throws DbException
     */
    public function quickUpdate($table, $object, $primaryKey = null)
    {
        $where = [];
        $fields = [];

        if ($primaryKey === null) {
            $primaryKey = $this->getTablePrimaryKey($table);
            if ($primaryKey === null) {
                throw new DbException('新数据表' . $table . '无主键，不支持按主键更新！');
            }
        }

        $vars = null;
        if (is_array($object)) {
            $vars = $object;
        } elseif (is_object($object)) {
            $vars = get_object_vars($object);
        } else {
            throw new DbException('更新的数据格式须为对象或数组');
        }

        foreach ($vars as $key => $value) {
            if (is_array($value) || is_object($value)) {
                continue;
            }

            if (is_array($primaryKey)) {

                if (in_array($key, $primaryKey)) {
                    $where[] = $this->quoteKey($key) . '=' . $this->quoteValue($value);
                    continue;
                }

            } else {

                // 主键不更新
                if ($key == $primaryKey) {
                    $where[] = $this->quoteKey($key) . '=' . $this->quoteValue($value);
                    continue;
                }
            }

            $fields[] = $this->quoteKey($key) . '=' . $this->quoteValue($value);
        }

        if (!$where) {
            throw new DbException('更新数据时未指定条件！');
        }

        $sql = 'UPDATE ' . $this->quoteKey($table) . ' SET ' . implode(',', $fields) . ' WHERE ' . implode(' AND ', $where);
        $statement = $this->execute($sql);
        $effectLines = $statement->rowCount();
        $statement->closeCursor();

        return $effectLines;
    }

    /**
     * 批量更新多个对象到数据库
     *
     * @param string $table 表名
     * @param array $objects $object 要更新的对象数组，对象属性需要和该表字段一致
     * @param null | string | array $primaryKey 主键或指定键名更新，未指定时自动取表的主键
     * @return int 影响的行数
     * @throws DbException
     */
    public function updateMany($table, $objects, $primaryKey = null)
    {
        return 0;
    }

    /**
     * 快速批量更新多个对象到数据库
     *
     * @param string $table 表名
     * @param array $objects 要快速批量更新的对象数组，对象属性需要和该表字段一致
     * @param null | string | array $primaryKey 主键或指定键名更新，未指定时自动取表的主键
     * @return int 影响的行数
     * @throws DbException
     */
    public function quickUpdateMany($table, $objects, $primaryKey = null)
    {
        return 0;
    }

    /**
     * 更新一个对象到数据库
     *
     * @param string $table 表名
     * @param array | object $object 要更新的对象，对象属性需要和该表字段一致
     * @return int 影响的行数
     * @throws DbException
     */
    public function replace($table, $object)
    {
        throw new DbException('Mssql 数据库不支持 Replace Into！');
    }

    /**
     * 批量更新多个对象到数据库
     *
     * @param string $table 表名
     * @param array $objects 要更新的对象数组，对象属性需要和该表字段一致
     * @return int 影响的行数
     * @throws DbException
     */
    public function replaceMany($table, $objects)
    {
        throw new DbException('Mssql 数据库不支持 Replace Into！');
    }

    /**
     * 快速更新一个对象到数据库
     *
     * @param string $table 表名
     * @param array | object $object 要更新的对象，对象属性需要和该表字段一致
     * @return int 影响的行数
     * @throws DbException
     */
    public function quickReplace($table, $object)
    {
        throw new DbException('Mssql 数据库不支持 Replace Into！');
    }

    /**
     * 快速批量更新多个对象到数据库
     *
     * @param string $table 表名
     * @param array $objects 要更新的对象数组，对象属性需要和该表字段一致
     * @return int 影响的行数
     * @throws DbException
     */
    public function quickReplaceMany($table, $objects)
    {
        throw new DbException('Mssql 数据库不支持 Replace Into！');
    }

    /**
     * 获取 insert 插入后产生的 id
     *
     * @return int
     */
    public function getLastInsertId()
    {
        return (int)$this->getValue('SELECT ISNULL(SCOPE_IDENTITY(), 0)');
    }

    /**
     * 获取当前数据库所有表信息
     *
     * @return array
     */
    public function getTables()
    {
        // SELECT * FROM sysobjects WHERE xType='u';
        // SELECT * FROM sys.objects WHERE type='U';
        // SELECT * FROM [INFORMATION_SCHEMA].[TABLES] WHERE [TABLE_TYPE] = 'BASE TABLE';
        $sql = 'SELECT 
                    a.name, 
                    g.value AS comment
                FROM sys.tables a
                LEFT JOIN sys.extended_properties g ON a.object_id = g.major_id AND g.minor_id = 0
                WHERE a.type=\'U\'';
        return $this->getObjects($sql);
    }

    /**
     * 获取当前数据库所有表名
     *
     * @return array
     */
    public function getTableNames()
    {
        // SELECT [TABLE_NAME] FROM [INFORMATION_SCHEMA].[TABLES] WHERE [TABLE_TYPE] = 'BASE TABLE'
        return $this->getValues('SELECT [name] FROM sys.tables WHERE [type] = \'U\'');
    }

    /**
     * 获取一个表的字段列表
     *
     * @param string $table 表名
     * @return array 对象数组
     * 字段对象典型结构
     * {
     *      'name' => '字段名',
     *      'type' => '类型',
     *      'length' => '长度',
     *      'precision' => '精度',
     *      'scale' => '长度',
     *      'comment' => '备注',
     *      'default' => '默认值',
     *      'nullAble' => '是否允许为空',
     * }
     */
    public function getTableFields($table)
    {
        $cacheKey = 'TableFields:' . $table;
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $sql = 'SELECT  
                    a.name,  
                    ISNULL(d.[value], \'\') AS [comment],  
                    b.name AS [type],  
                    a.length AS [length],  
                    ISNULL(COLUMNPROPERTY(a.id, a.name, \'Scale\'), 0) AS [scale],  
                    a.isnullable AS [null_able],
                    c.text AS [default]
                FROM syscolumns a  
                LEFT JOIN systypes b ON a.xtype = b.xusertype  
                LEFT JOIN syscomments c ON a.cdefault = c.id  
                LEFT JOIN sys.extended_properties d ON a.id = d.major_id AND a.colid = d.minor_id AND d.name = \'MS_Description\'  
                WHERE a.id=object_id(\'' . $table . '\')';
        $fields = $this->getObjects($sql);

        $data = [];
        foreach ($fields as $field) {
            $data[$field->name] = [
                'name' => $field->name,
                'type' => $field->type,
                'length' => $field->length,
                'precision' => 0,
                'scale' => $field->scale,
                'comment' => $field->comment,
                'default' => $field->default,
                'nullAble' => $field->null_able ? true : false,
            ];
        }

        $this->cache[$cacheKey] = $data;
        return $data;
    }

    /**
     * 获取指定表的主银
     *
     * @param string $table 表名
     * @return string | array | null
     */
    public function getTablePrimaryKey($table)
    {
        $cacheKey = 'TablePrimaryKey:' . $table;
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $sql = 'SELECT COL_NAME(a.parent_obj, c.colid) 
                FROM sysobjects a
                LEFT JOIN sysindexes b ON a.name = b.name
                LEFT JOIN sysindexkeys c ON b.id = c.id AND b.indid = c.indid
                WHERE a.xtype=\'PK\' AND a.parent_obj=OBJECT_ID(\'' . $table . '\')';
        $primaryKeys = $this->getValues($sql);

        $primaryKey = null;
        $count = count($primaryKeys);
        if ($count > 1) {
            $primaryKey = $primaryKeys;
        } elseif ($count == 1) {
            $primaryKey = $primaryKeys[0];
        }

        $this->cache[$cacheKey] = $primaryKey;
        return $primaryKey;
    }

    /**
     * 删除表
     *
     * @param string $table 表名
     */
    public function dropTable($table)
    {
        $statement = $this->execute('DROP TABLE ' . $this->quoteKey($table));
        $statement->closeCursor();
    }

    /**
     * 处理插入数据库的字段名或表名
     *
     * @param string $field
     * @return string
     */
    public function quoteKey($field)
    {
        if (strpos($field, '.')) {
            $field = str_replace('.', '].[', $field);
        }

        return '[' . $field . ']';
    }

    /**
     * 处理插入数据库的字符串值，防注入, 仅处理敏感字符，不加外层引号，
     * 与 quote 方法的区别可以理解为 quote 比 escape 多了最外层的引号
     *
     * @param string $value
     * @return string
     */
    public function escape($value)
    {
        $value = str_replace('\'', '\'\'', $value);

        return $value;
    }


}
