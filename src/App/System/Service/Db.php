<?php

namespace Be\App\System\Service;

use Be\System\Exception\ServiceException;
use Be\System\Be;
use Be\Util\Str;

class Db
{

    /**
     * 获取指定应用下的相关表
     *
     * @param string $app 应用
     * @param string $dbName 库名
     * @return array
     * @throws \Exception
     */
    public function getTables($app, $dbName = 'master')
    {
        $tables = [];
        $prefix = Str::camel2Underline($app) . '_';
        $db = Be::getDb($dbName);
        $tableNames = $db->getValues('SHOW TABLES LIKE \'' . $prefix . '%\'');
        if ($tableNames) {
            foreach ($tableNames as $tableName) {
                $tables[] = Be::newTable($tableName, $dbName);
            }
        }
        return $tables;
    }

    /**
     * 更新 表属性 TableProperty
     *
     * @param string $tableName 表名
     * @param string $dbName 库名
     * @throws \Exception
     */
    public function updateTableProperty($tableName, $dbName = 'master')
    {
        $db = Be::getDb($dbName);

        $fields = $db->getTableFields($tableName);
        $primaryKey = $db->getTablePrimaryKey($tableName);

        foreach ($fields as &$field) {
            if (strpos($field['extra'], 'auto_increment') !== false) {
                $field['autoIncrement'] = 1;
            } else {
                $field['autoIncrement'] = 0;
            }

            if (in_array($field['type'], [
                'int', 'tinyint', 'smallint', 'mediumint', 'bigint', 'float', 'double', 'decimal'
            ])) {
                $field['isNumber'] = 1;
            } else {
                $field['isNumber'] = 0;
            }
        }
        unset($field);

        $code = '<?php' . "\n";
        $code .= 'namespace Be\\Cache\\System\\TableProperty\\' . $dbName . ';' . "\n";
        $code .= "\n";
        $code .= 'class ' . $tableName . ' extends \\Be\\System\\Db\\TableProperty' . "\n";
        $code .= '{' . "\n";
        $code .= '    protected $_dbName = \'' . $dbName . '\'; // 数据库名' . "\n";
        $code .= '    protected $_tableName = \'' . $tableName . '\'; // 表名' . "\n";
        $code .= '    protected $_primaryKey = ' . var_export($primaryKey, true) . '; // 主键' . "\n";
        $code .= '    protected $_fields = ' . var_export($fields, true) . '; // 字段列表' . "\n";
        $code .= '}' . "\n";
        $code .= "\n";

        $path = Be::getRuntime()->getCachePath() . '/System/TableProperty/' . $dbName . '/' . $tableName . '.php';
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        file_put_contents($path, $code, LOCK_EX);
        chmod($path, 0755);

        @include_once $path;
    }

    /**
     * 更新表 Table
     *
     * @param string $tableName 表名
     * @param string $dbName 库名
     * @throws \Exception
     */
    public function updateTable($tableName, $dbName = 'master')
    {
        $tableProperty = Be::getTableProperty($tableName, $dbName);

        $code = '<?php' . "\n";
        $code .= 'namespace Be\\Cache\\System\\Table\\' . $dbName . ';' . "\n";
        $code .= "\n";
        $code .= 'class ' . $tableName . ' extends \\Be\\System\\Db\\Table' . "\n";
        $code .= '{' . "\n";
        $code .= '    protected $_dbName = \'' . $dbName . '\'; // 数据库名' . "\n";
        $code .= '    protected $_tableName = \'' . $tableName . '\'; // 表名' . "\n";
        $code .= '    protected $_primaryKey = ' . var_export($tableProperty->getPrimaryKey(), true) . '; // 主键' . "\n";
        $code .= '    protected $_fields = [\'' . implode('\',\'', array_column($tableProperty->getFields(), 'name')) . '\']; // 字段列表' . "\n";
        $code .= '}' . "\n";
        $code .= "\n";

        $path = Be::getRuntime()->getCachePath() . '/System/Table/' . $dbName . '/' . $tableName . '.php';
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        file_put_contents($path, $code, LOCK_EX);
        chmod($path, 0755);

        @include_once $path;
    }

    /**
     * 更新 行记灵对象 Tuple
     *
     * @param string $tableName 表名
     * @param string $dbName 库名
     * @throws \Exception
     */
    public function updateTuple($tableName, $dbName = 'master')
    {
        $tableProperty = Be::getTableProperty($tableName, $dbName);
        $fields = $tableProperty->getFields();

        $code = '<?php' . "\n";
        $code .= 'namespace Be\\Cache\\System\\Tuple\\' . $dbName . ';' . "\n";
        $code .= "\n";
        $code .= 'class ' . $tableName . ' extends \\Be\\System\\Db\\Tuple' . "\n";
        $code .= '{' . "\n";
        $code .= '    protected $_dbName = \'' . $dbName . '\'; // 数据库名' . "\n";
        $code .= '    protected $_tableName = \'' . $tableName . '\'; // 表名' . "\n";
        $code .= '    protected $_primaryKey = ' . var_export($tableProperty->getPrimaryKey(), true) . '; // 主键' . "\n";

        foreach ($fields as $field) {
            if ($field['isNumber']) {
                $code .= '    public $' . $field['name'] . ' = ' . ($field['default'] === null ? 0 : $field['default']). ';';
            } else {
                $code .= '    public $' . $field['name'] . ' = \'' . ($field['default'] === null ? '' : $field['default']) . '\';';
            }

            if ($field['comment']) $code .= ' // ' . $field['comment'];
            $code .= "\n";
        }

        $code .= '}' . "\n";
        $code .= "\n";

        $path = Be::getRuntime()->getCachePath() . '/System/Tuple/' . $dbName . '/' . $tableName . '.php';
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        file_put_contents($path, $code, LOCK_EX);
        chmod($path, 0755);

        @include_once $path;
    }

}
