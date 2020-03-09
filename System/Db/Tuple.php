<?php
namespace Be\System\Db;

use Be\System\Be;
use Be\System\CacheProxy;

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
     * 应用名
     *
     * @var string
     */
    protected $_appName = '';

    /**
     * 表全名
     *
     * @var string
     */
    protected $_tableName = '';

    /**
     * 主键
     *
     * @var string | array
     */
    protected $_primaryKey = '';

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
     * 加载记录
     *
     * @param string|int|array $field 要加载数据的键名，$val == null 时，为指定的主键值加载，
     * @param string $value 要加载的键的值
     * @return \Be\System\Db\Tuple | false
     * @throws DbException
     */
    public function load($field, $value = null)
    {
        $db = Be::getDb($this->_dbName);

        $sql = null;
        $values = [];

        if ($value === null) {
            if (is_array($field)) {
                $sql = 'SELECT * FROM ' . $db->quoteKey($this->_tableName) . ' WHERE';
                foreach ($field as $key => $val) {
                    $sql .= ' ' . $db->quoteKey($key) . '=? AND';
                    $values[] = $val;
                }
                $sql = substr($sql, 0, -4);
            } elseif (is_numeric($field)) {
                $sql = 'SELECT * FROM ' . $db->quoteKey($this->_tableName) . ' WHERE ' . $db->quoteKey( $this->_primaryKey) . ' = \'' . intval($field) . '\'';
            } elseif (is_string($field)) {
                $sql = 'SELECT * FROM ' . $db->quoteKey($this->_tableName) . ' WHERE ' . $field;
            }
        } else {
            if (is_array($field)) {
                throw new DbException('Tuple->load() 方法参数错误！');
            }
            $sql = 'SELECT * FROM ' . $db->quoteKey($this->_tableName) . ' WHERE ' . $db->quoteKey($field) . '=?';
            $values[] = $value;
        }

        $tuple = $db->getObject($sql, $values);

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

        $primaryKey = $this->_primaryKey;
        if ($this->$primaryKey) {
            $db->update($this->_tableName, $this, $this->_primaryKey);
        } else {
            $db->insert($this->_tableName, $this);
            $this->$primaryKey = $db->getLastInsertId();
        }

        return $this;
    }

    /**
     * 删除指定主键值的记录
     *
     * @param int $id 主键值
     * @return Tuple
     * @throws DbException
     */
    public function delete($id = null)
    {
        $primaryKey = $this->_primaryKey;
        if ($id === null) $id = $this->$primaryKey;

        if ($id === null) {
            throw new DbException('参数缺失, 请指定要删除记录的编号！');
        }

        $db = Be::getDb($this->_dbName);
        $statement = $db->execute('DELETE FROM ' . $db->quoteKey($this->_tableName) . ' WHERE ' . $db->quoteKey($this->_primaryKey) . '=?', array($id));
        $statement->closeCursor();

        return $this;
    }

    /**
     * 自增某个字段
     *
     * @param string $field 字段名
     * @param int $step 自增量
     * @return Tuple
     */
    public function increment($field, $step = 1)
    {
        $db = Be::getDb($this->_dbName);
        $primaryKey = $this->_primaryKey;
        $id = $this->$primaryKey;
        $sql = 'UPDATE ' . $db->quoteKey($this->_tableName) . ' SET ' . $db->quoteKey($field) . '=' . $db->quoteKey($field) . '+' . $step . ' WHERE ' . $db->quoteKey($this->_primaryKey) . '=?';

        $statement = $db->execute($sql, array($id));
        $statement->closeCursor();

        return $this;
    }

    /**
     * 自减某个字段
     *
     * @param string $field 字段名
     * @param int $step 自减量
     * @return Tuple
     */
    public function decrement($field, $step = 1)
    {
        $db = Be::getDb($this->_dbName);

        $primaryKey = $this->_primaryKey;
        $id = $this->$primaryKey;
        $sql = 'UPDATE ' . $db->quoteKey($this->_tableName) . ' SET ' . $db->quoteKey($field) . '=' . $db->quoteKey($field) . '-' . $step . ' WHERE ' . $db->quoteKey($this->_primaryKey) . '=?';

        $statement = $db->execute($sql, array($id));
        $statement->closeCursor();

        return $this;
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
     * @return string
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
    public function toArray() {
        $array = get_object_vars($this);
        unset($array['_dbName'], $array['_appName'], $array['_tableName'], $array['_primaryKey']);

        return $array;
    }

    /**
     * 转成简单对象
     *
     * @return Object
     */
    public function toObject() {
        return (Object) $this->toArray();
    }
}
