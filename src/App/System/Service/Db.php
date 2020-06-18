<?php

namespace Be\App\System\Service;

use Be\System\Exception\ServiceException;
use Be\System\Be;
use Be\Util\Str;

class Db extends \Be\System\Service
{


    public function getTables($app)
    {
        $tables = [];
        $prefix = Str::camel2Underline($app) . '_';
        $db = Be::getDb();
        $tableNames = $db->getValues('SHOW TABLES LIKE \'' . $prefix . '%\'');
        if ($tableNames) {
            foreach ($tableNames as $tableName) {
                $tables[] = Be::newTable($tableName);
            }
        }
        return $tables;
    }


    public function updateTableProperty($tableName, $db = 'master')
    {
        $db = Be::getDb($db);

        $fields = $db->getTableFields($tableName);
        $primaryKey = $db->getTablePrimaryKey($tableName);

        foreach ($fields as &$field) {
            $field = (array)$field;
            if (strpos($field['extra'], 'auto_increment')) {
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
        $code .= 'namespace Be\\Cache\\System\\TableProperty\\' . $db . ';' . "\n";
        $code .= "\n";
        $code .= 'class ' . $tableName . ' extends \\Be\\System\\Db\\TableProperty' . "\n";
        $code .= '{' . "\n";
        $code .= '    protected $_dbName = \'' . $db . '\'; // 数据库名' . "\n";
        $code .= '    protected $_tableName = \'' . $tableName . '\'; // 表名' . "\n";
        $code .= '    protected $_primaryKey = ' . var_export($primaryKey, true) . '; // 主键' . "\n";
        $code .= '    protected $_fields = ' . var_export($fields, true) . '; // 字段列表' . "\n";
        $code .= '}' . "\n";
        $code .= "\n";

        $path = Be::getRuntime()->getCachePath() . '/System/TableProperty/' . $db . '/' . $tableName . '.php';
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        file_put_contents($path, $code, LOCK_EX);
        chmod($path, 0755);

        return true;
    }


    /**
     * 更新表
     *
     * @param string $tableName 要表新的表名
     * @throws |Exception
     */
    public function updateTable($tableName, $db = 'master')
    {
        $tableProperty = Be::getTableProperty($tableName, $db);

        $code = '<?php' . "\n";
        $code .= 'namespace Be\\Cache\\System\\Table\\' . $db . ';' . "\n";
        $code .= "\n";
        $code .= 'class ' . $tableName . ' extends \\Be\\System\\Db\\Table' . "\n";
        $code .= '{' . "\n";
        $code .= '    protected $_dbName = \'' . $db . '\'; // 数据库名' . "\n";
        $code .= '    protected $_tableName = \'' . $tableName . '\'; // 表名' . "\n";
        $code .= '    protected $_primaryKey = ' . var_export($tableProperty->getPrimaryKey(), true) . '; // 主键' . "\n";
        $code .= '    protected $_fields = [\'' . implode('\',\'', array_column($tableProperty->getFields(), 'name')) . '\']; // 字段列表' . "\n";
        $code .= '}' . "\n";
        $code .= "\n";

        $path = Be::getRuntime()->getCachePath() . '/System/Table/' . $db . '/' . $tableName . '.php';
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        file_put_contents($path, $code, LOCK_EX);
        chmod($path, 0755);
    }


    /**
     * 更新 数据库行记灵对象
     *
     * @param string $name 数据库行记灵对象名称
     * @throws |Exception
     */
    public function updateTuple($tableName, $db = 'master')
    {
        $tableProperty = Be::getTableProperty($tableName, $db);
        $fields = $tableProperty->getFields();

        $code = '<?php' . "\n";
        $code .= 'namespace Be\\Cache\\System\\Tuple\\' . $db . ';' . "\n";
        $code .= "\n";
        $code .= 'class ' . $tableName . ' extends \\Be\\System\\Db\\Tuple' . "\n";
        $code .= '{' . "\n";
        $code .= '    protected $_dbName = \'' . $db . '\'; // 数据库名' . "\n";
        $code .= '    protected $_tableName = \'' . $tableName . '\'; // 表名' . "\n";
        $code .= '    protected $_primaryKey = ' . var_export($tableProperty->getPrimaryKey(), true) . '; // 主键' . "\n";

        foreach ($fields as $field) {
            if ($field['isNumber']) {
                $code .= '    public $' . $field->name . ' = ' . $field['default'] . ';';
            } else {
                $code .= '    public $' . $field->name . ' = \'' . $field['default'] . '\';';
            }

            if ($field->comment) $code .= ' // ' . $field->comment;
            $code .= "\n";
        }

        $code .= '}' . "\n";
        $code .= "\n";

        $path = Be::getRuntime()->getCachePath() . '/System/Tuple/' . $db . '/' . $tableName . '.php';
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        file_put_contents($path, $code, LOCK_EX);
        chmod($path, 0755);
    }

}
