<?php
namespace Be\System\Db;

/**
 * Class TableProperty
 * @package \Be\System\Db
 */
class TableProperty
{

    /**
     * 应用名
     *
     * @var string
     */
    protected $_app = '';

    /**
     * 表名
     *
     * @var string
     */
    protected $_tableName = '';

    /**
     * 字段明细列表
     *
     * @var array
     */
    protected $_fields = [];


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
     * 获取字段明细列表
     *
     * @return array
     */
    public function getFields()
    {
        return $this->_fields;
    }

    /**
     * 获取指定字段
     *
     * @param string $fieldName 字段名
     * @return array
     */
    public function getField($fieldName)
    {
        return isset($this->_fields[$fieldName]) ? $this->_fields[$fieldName] : null;
    }


}
