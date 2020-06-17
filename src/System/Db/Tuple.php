<?php

namespace Be\System\Db;

use Be\System\Be;
use Be\System\CacheProxy;
use Be\System\Exception\DbException;

/**
 * 数据库表行记录
 */
abstract class Tuple
{
    /**
     * 默认查询的数据库
     *
     * @var string
     */
    protected $_dbName = 'master';

    /**
     * 表全名
     *
     * @var string
     */
    protected $_tableName = '';

    /**
     * 主键
     *
     * @var null | string | array
     */
    protected $_primaryKey = null;

    /**
     * 启动缓存代理
     *
     * @param int $expire 超时时间
     * @return CacheProxy | Mixed
     */
    public function withCache($expire = 600)
    {
        return new CacheProxy($this, $expire);
    }

    /**
     * 绑定一个数据源， GET, POST, 或者一个数组, 对象
     *
     * @param string | array | object $data 要绑定的数据对象
     * @return \Be\System\Db\Tuple | bool
     * @throws DbException
     */
    public function bind($data)
    {
        if (!is_object($data) && !is_array($data)) {
            throw new DbException('绑定失败，不合法的数据源！');
        }

        if (is_object($data)) $data = get_object_vars($data);

        $properties = get_object_vars($this);

        foreach ($properties as $key => $value) {
            if (isset($data[$key])) {
                $val = $data[$key];
                $this->$key = $val;
            }
        }

        return $this;
    }

    /**
     * 按主锓加载记录
     *
     * @param string | array $primaryKeyValue 主锓的值，当为数组时格式为键值对
     * @return \Be\System\Db\Tuple
     * @throws DbException
     */
    public function load($primaryKeyValue)
    {
        if ($this->_primaryKey === null) {
            throw new DbException('表' . $this->_tableName . '无主键，不支持按主键载入数据！');
        }

        $db = Be::getDb($this->_dbName);

        $tuple = null;
        if (is_array($primaryKeyValue)) {
            if (!is_array($this->_primaryKey)) {
                throw new DbException('表' . $this->_tableName . '非复合主键，不支持按复合主键载入数据！');
            }

            $keys = [];
            $values = [];
            foreach ($this->_primaryKey as $primaryKey) {
                $keys[] = $db->quoteKey($primaryKey) . '=?';

                if (!isset($primaryKeyValue[$primaryKey])) {
                    throw new DbException('表' . $this->_tableName . '按复合主键载入数据时未指定主键' . $primaryKey . '的值！');
                }

                $values[] = $primaryKeyValue[$primaryKey];
            }

            $sql = 'SELECT * FROM ' . $db->quoteKey($this->_tableName) . ' WHERE ' . implode(' AND ', $keys);
            $tuple = $db->getObject($sql, $values);

        } else {
            if (is_array($this->_primaryKey)) {
                throw new DbException('表' . $this->_tableName . '是复合主键，不支持章个主键载入数据！');
            }

            $sql = 'SELECT * FROM ' . $db->quoteKey($this->_tableName) . ' WHERE ' . $db->quoteKey($this->_primaryKey) . ' = ?';
            $tuple = $db->getObject($sql, $primaryKeyValue);
        }

        if (!$tuple) {
            throw new DbException('未找到指定数据记录！');
        }

        return $this->bind($tuple);
    }

    /**
     * 按条件加载记录
     *
     * @param string|int|array $field 要加载数据的键名，$val == null 时，为指定的主键值加载，
     * @param string $value 要加载的键的值
     * @return \Be\System\Db\Tuple | false
     * @throws DbException
     */
    public function loadBy($field, $value = null)
    {
        $db = Be::getDb($this->_dbName);

        $tuple = null;
        if ($value === null) {
            if (is_array($field)) {
                $keys = [];
                $values = [];
                foreach ($field as $key => $val) {
                    $keys[] = $db->quoteKey($key) . '=?';
                    $values[] = $val;
                }
                $sql = 'SELECT * FROM ' . $db->quoteKey($this->_tableName) . ' WHERE ' . implode(' AND ', $keys);
                $tuple = $db->getObject($sql, $values);
            } else {
                $sql = 'SELECT * FROM ' . $db->quoteKey($this->_tableName) . ' WHERE ' . $field;
                $tuple = $db->getObject($sql);
            }
        } else {
            if (is_array($field)) {
                throw new DbException('Tuple->load() 方法参数错误！');
            }
            $sql = 'SELECT * FROM ' . $db->quoteKey($this->_tableName) . ' WHERE ' . $db->quoteKey($field) . '=?';
            $tuple = $db->getObject($sql, [$value]);
        }

        if (!$tuple) {
            throw new DbException('未找到指定数据记录！');
        }

        return $this->bind($tuple);
    }

    /**
     * 保存数据到数据库
     *
     * @return Tuple
     */
    public function save()
    {
        $db = Be::getDb($this->_dbName);
        if ($this->_primaryKey === null) {
            $db->insert($this->_tableName, $this);
        } elseif (is_array($this->_primaryKey)) {
            $update = true;
            foreach ($this->_primaryKey as $primaryKey) {
                if (!$this->$primaryKey) {
                    $update = false;
                    break;
                }
            }
            if ($update) {
                $db->update($this->_tableName, $this, $this->_primaryKey);
            } else {
                $db->insert($this->_tableName, $this);
                $tableProperty = Be::getTableProperty($this->_tableName);
                foreach ($this->_primaryKey as $primaryKey) {
                    $field = $tableProperty->getField($primaryKey);
                    if (isset($field['autoIncrement']) && $field['autoIncrement']) {
                        $this->$primaryKey = $db->getLastInsertId();
                        break;
                    }
                }
            }
        } else {
            $primaryKey = $this->_primaryKey;
            if ($this->$primaryKey) {
                $db->update($this->_tableName, $this, $this->_primaryKey);
            } else {
                $db->insert($this->_tableName, $this);
                $tableProperty = Be::getTableProperty($this->_tableName);
                $field = $tableProperty->getField($primaryKey);
                if (isset($field['autoIncrement']) && $field['autoIncrement']) {
                    $this->$primaryKey = $db->getLastInsertId();
                }
            }
        }
        return $this;
    }

    /**
     * 删除指定主键值的记录
     *
     * @param int $primaryKeyValue 主键值
     * @return Tuple
     * @throws DbException
     */
    public function delete($primaryKeyValue = null)
    {
        if ($this->_primaryKey === null) {
            throw new DbException('表 ' . $this->_tableName . ' 无主键, 不支持按主键删除！');
        }


        if ($primaryKeyValue === null) {
            if ($this->_primaryKey === null) {
                throw new DbException('参数缺失, 请指定要删除记录的编号！');
            } elseif (is_array($this->_primaryKey)) {
                $primaryKeyValue = [];
                foreach ($this->_primaryKey as $primaryKey) {
                    $primaryKeyValue[$primaryKey] = $this->$primaryKey;
                }
            } else {
                $primaryKeyValue = $this->_primaryKey;
            }
        }

        $db = Be::getDb($this->_dbName);
        if (is_array($primaryKeyValue)) {
            $keys = [];
            $values = [];
            foreach ($primaryKeyValue as $key => $value) {
                $keys[] = $db->quoteKey($key) . '=?';
                $values[] = $value;
            }
            $db->query('DELETE FROM ' . $db->quoteKey($this->_tableName) . ' WHERE ' . implode(' AND ', $keys), [$values]);
        } else {
            $db->query('DELETE FROM ' . $db->quoteKey($this->_tableName) . ' WHERE ' . $db->quoteKey($this->_primaryKey) . '=?', [$primaryKeyValue]);
        }
        return $this;
    }

    /**
     * 自增某个字段
     *
     * @param string $field 字段名
     * @param int $step 自增量
     * @return Tuple
     * @throws DbException
     */
    public function increment($field, $step = 1)
    {
        if ($this->_primaryKey === null) {
            throw new DbException('表 ' . $this->_tableName . ' 无主键, 不支持字段自增！');
        }

        $db = Be::getDb($this->_dbName);
        if (is_array($this->_primaryKey)) {
            $keys = [];
            $values = [];
            foreach ($this->_primaryKey as $primaryKey) {
                $keys[] = $db->quoteKey($primaryKey) . '=?';
                $values[] = $this->$primaryKey;
            }
            $sql = 'UPDATE ' . $db->quoteKey($this->_tableName) . ' SET ' . $db->quoteKey($field) . '=' . $db->quoteKey($field) . '+' . $step . ' WHERE ' . implode(' AND ', $keys);
            $db->query($sql, [$values]);

        } else {
            $primaryKey = $this->_primaryKey;
            $primaryKeyValue = $this->$primaryKey;
            $sql = 'UPDATE ' . $db->quoteKey($this->_tableName) . ' SET ' . $db->quoteKey($field) . '=' . $db->quoteKey($field) . '+' . $step . ' WHERE ' . $db->quoteKey($this->_primaryKey) . '=?';
            $db->query($sql, [$primaryKeyValue]);
        }
        return $this;
    }

    /**
     * 自减某个字段
     *
     * @param string $field 字段名
     * @param int $step 自减量
     * @return Tuple
     * @throws DbException
     */
    public function decrement($field, $step = 1)
    {
        if ($this->_primaryKey === null) {
            throw new DbException('表 ' . $this->_tableName . ' 无主键, 不支持字段自减！');
        }

        $db = Be::getDb($this->_dbName);
        if (is_array($this->_primaryKey)) {
            $keys = [];
            $values = [];
            foreach ($this->_primaryKey as $primaryKey) {
                $keys[] = $db->quoteKey($primaryKey) . '=?';
                $values[] = $this->$primaryKey;
            }
            $sql = 'UPDATE ' . $db->quoteKey($this->_tableName) . ' SET ' . $db->quoteKey($field) . '=' . $db->quoteKey($field) . '-' . $step . ' WHERE ' . implode(' AND ', $keys);
            $db->query($sql, [$values]);

        } else {
            $primaryKey = $this->_primaryKey;
            $primaryKeyValue = $this->$primaryKey;
            $sql = 'UPDATE ' . $db->quoteKey($this->_tableName) . ' SET ' . $db->quoteKey($field) . '=' . $db->quoteKey($field) . '-' . $step . ' WHERE ' . $db->quoteKey($this->_primaryKey) . '=?';
            $db->query($sql, [$primaryKeyValue]);
        }
        return $this;
    }

    /**
     * 获取数据库名
     *
     * @return string
     */
    public function getDbName()
    {
        return $this->_dbName;
    }

    /**
     * 获取表名
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->_tableName;
    }

    /**
     * 获取主键名
     *
     * @return null | string | array
     */
    public function getPrimaryKey()
    {
        return $this->_primaryKey;
    }

    /**
     * 转成简单数组
     *
     * @return array
     */
    public function toArray()
    {
        $array = get_object_vars($this);
        unset($array['_dbName'], $array['_tableName'], $array['_primaryKey']);

        return $array;
    }

    /**
     * 转成简单对象
     *
     * @return Object
     */
    public function toObject()
    {
        return (Object)$this->toArray();
    }

}


