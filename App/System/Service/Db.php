<?php

namespace Be\App\System\Service;

use Be\System\Service\ServiceException;
use Be\System\Be;
use Be\Util\Str;

class Db extends \Be\System\Service
{


    public function getAppNames()
    {
        return ['Cms', 'System'];
    }

    public function getTables($app)
    {
        $tables = [];

        $prefix = Str::camel2Underline($app).'_';
        $len = strlen($prefix);

        $path = Be::getRuntime()->getCachePath() . '/System/Table/';
        if (file_exists($path)) {
            if (is_dir($path)) {
                $handle = opendir($path);
                while (($file = readdir($handle)) !== false) {
                    if ($file != '.' && $file != '..' && !is_dir($path . '/' . $file)) {

                        if (substr($file, 0, $len) == $prefix) {
                            $table = Be::newTable($file);
                            if ($table->getAppName() == $app) {
                                $tables[$file] = $table;
                            }
                        }
                    }
                }
                closedir($handle);
            }
        }

        $path = Be::getRuntime()->getRootPath() . '/App/' . $app . '/Table/';
        if (file_exists($path)) {
            if (is_dir($path)) {
                $handle = opendir($path);
                while (($file = readdir($handle)) !== false) {
                    if ($file != '.' && $file != '..' && !is_dir($path . '/' . $file) && !isset($tables[$file])) {
                        $tables[$file] = Be::newTable($file);
                    }
                }
                closedir($handle);
            }
        }

        ksort($handle);

        return $tables;
    }


    public function updateTableConfig($tableName, $fields)
    {
        $db = Be::getDb();
        if (!$db->getValue('SHOW TABLES LIKE \'' . $tableName . '\'')) {
            throw new ServiceException('未找到名称为 ' . $tableName . ' 的数据库表！');
        }

        $fields = $db->getObjects('SHOW FULL FIELDS FROM ' . $tableName);

        $primaryKey = 'id';
        foreach ($fields as $field) {
            if ($field->Key == 'PRI') {
                $primaryKey = $field->Field;
                break;
            }
        }

        $code = '<?php' . "\n";
        $code .= 'namespace Be\\Data\\System\\TableConfig;' . "\n";
        $code .= "\n";
        $code .= 'class ' . $tableName . ' extends \\Be\\System\\Db\\TableConfig' . "\n";
        $code .= '{' . "\n";
        $code .= '    protected $_app = \'' . $this->getAppNameByTableName($tableName) . '\'; // 应用名' . "\n";
        $code .= '    protected $_tableName = \'' . $tableName . '\'; // 表名' . "\n";
        $code .= '    protected $_primaryKey = \'' . $primaryKey . '\'; // 主键' . "\n";
        $code .= '    protected $_fields = ' . var_export($fields, true) . '; // 字段列表' . "\n";
        $code .= '}' . "\n";
        $code .= "\n";

        $path = Be::getRuntime()->getDataPath() . '/System/TableConfig/' . $tableName . '.php';
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        file_put_contents($path, $code, LOCK_EX);
        chmod($path, 0755);

        $this->updateTable($tableName);

        $this->updateTuple($tableName);

        return true;
    }


    /**
     * 更新表
     *
     * @param string $tableName 要表新的表名
     * @throws |Exception
     */
    public function updateTable($tableName)
    {
        $db = Be::getDb();
        if (!$db->getValue('SHOW TABLES LIKE \'' . $tableName . '\'')) {
            throw new ServiceException('未找到名称为 ' . $tableName . ' 的数据库表！');
        }

        $fields = $db->getObjects('SHOW FULL FIELDS FROM ' . $tableName);
        $formattedFields = [];
        $primaryKey = 'id';
        foreach ($fields as $field) {
            if ($field->Key == 'PRI') {
                $primaryKey = $field->Field;
            }

            $formattedFields[] = $field->Field;
        }

        //$formattedFields = $this->formatTableFields($app, $name, $fields);

        $code = '<?php' . "\n";
        $code .= 'namespace Be\\Cache\\System\\Table;' . "\n";
        $code .= "\n";
        $code .= 'class ' . $tableName . ' extends \\Be\\System\\Db\\Table' . "\n";
        $code .= '{' . "\n";
        $code .= '    protected $_app = \'' . $this->getAppNameByTableName($tableName) . '\'; // 应用名' . "\n";
        $code .= '    protected $_tableName = \'' . $tableName . '\'; // 表名' . "\n";
        $code .= '    protected $_primaryKey = \'' . $primaryKey . '\'; // 主键' . "\n";
        $code .= '    protected $_fields = [\'' . implode('\',\'', $formattedFields) . '\']; // 字段列表' . "\n";
        $code .= '}' . "\n";
        $code .= "\n";

        $path = Be::getRuntime()->getCachePath() . '/System/Table/' . $tableName . '.php';
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        file_put_contents($path, $code, LOCK_EX);
        chmod($path, 0755);
    }


    /**
     * 更新 数据库行记灵对象
     *
     * @param string $name 数据库行记灵对象名称
     * @throws |Exception
     */
    public function updateTuple($tableName)
    {
        $db = Be::getDb();
        if (!$db->getValue('SHOW TABLES LIKE \'' . $tableName . '\'')) {
            throw new ServiceException('未找到名称为 ' . $tableName . ' 的数据库表！');
        }

        $fields = $db->getObjects('SHOW FULL FIELDS FROM ' . $tableName);

        $primaryKey = 'id';
        foreach ($fields as $field) {
            if ($field->Key == 'PRI') {
                $primaryKey = $field->Field;
                break;
            }
        }

        $formattedFields = $this->formatTableFields($tableName, $fields);

        $code = '<?php' . "\n";
        $code .= 'namespace Be\\Cache\\System\\Tuple;' . "\n";
        $code .= "\n";
        $code .= 'class ' . $tableName . ' extends \\Be\\System\\Db\\Tuple' . "\n";
        $code .= '{' . "\n";
        $code .= '    protected $_app = \'' . $this->getAppNameByTableName($tableName) . '\'; // 应用名' . "\n";
        $code .= '    protected $_tableName = \'' . $tableName . '\'; // 表名' . "\n";
        $code .= '    protected $_primaryKey = \'' . $primaryKey . '\'; // 主键' . "\n";

        foreach ($formattedFields as $key => $field) {
            $code .= '    public $' . $field['field'] . ' = ' . ($field['isNumber'] ? $field['default'] : ('\'' . $field['default'] . '\'')) . ';';
            if ($field->comment) $code .= ' // ' . $field['comment'];
            $code .= "\n";
        }

        $code .= '}' . "\n";
        $code .= "\n";

        $path = Be::getRuntime()->getCachePath() . '/System/Tuple/' . $tableName . '.php';
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        file_put_contents($path, $code, LOCK_EX);
        chmod($path, 0755);
    }


    protected function getAppNameByTableName($tableName) {
        $appNames = $this->getAppNames();

        $tableNames = explode('_', $tableName);
        $n = count($tableNames);
        $appName = '';
        for ($i = 0; $i < $n; $i++) {
            $appName .= ucfirst($tableNames[$i]);
            if (in_array($appName, $appNames)) {
                return $appName;
            }
        }

        return ucfirst($tableNames[0]);
    }

    public function formatTableFields($tableName, $fields)
    {

        $tableConfig = Be::newTableConfig($tableName);

        $formattedFields = array();

        foreach ($fields as $field) {

            $type = $field->Type;
            $typeLength = 0;
            $unsigned = strpos($field->Type, 'unsigned') !== false;

            $pos = strpos($field->Type, '(');
            if ($pos !== false) {
                $type = substr($field->Type, 0, $pos);
                $typeLength = substr($field->Type, $pos + 1, strpos($field->Type, ')') - $pos - 1);
            }

            //if (!is_numeric($typeLength)) $typeLength = -1;

            $numberTypes = array('int', 'mediumint', 'tinyint', 'smallint', 'bigint', 'decimal', 'float', 'double', 'real', 'bit', 'boolean', 'serial');
            $isNumber = in_array($type, $numberTypes);

            $default = null;
            if ($isNumber) {
                $default = $field->Default ? $field->Default : 0;
            } else {
                $default = $field->Default ? addslashes($field->Default) : '';
            }

            $extra = $field->Extra;

            $comment = addslashes($field->Comment);

            $optionType = 'null';
            $optionData = '';

            if ($type == 'enum') {
                $optionType = 'array';
                $optionData = str_replace(',', "\n", $typeLength);
            }

            $name = $field->Field;
            $disable = false;
            $listsEnable = true;
            $detailEnable = true;
            $createEnable = true;
            $editEnable = true;
            $format = '';


            $configField = $tableConfig->getField($field->Field);
            if ($configField) {
                $name = $configField['name'];

                if (isset($configField['optionType']) &&
                    in_array($configField['optionType'], array('null', 'array', 'sql')) &&
                    isset($configField['optionData'])
                ) {

                    $optionType = $configField['optionType'];
                    $optionData = $configField['optionData'];
                }

                if (isset($configField['format']) && $configField['format']) {
                    $format = $configField['format'];
                }

                if (isset($configField['disable'])) {
                    $disable = $configField['disable'] ? true : false;
                }

                if (isset($configField['listsEnable'])) {
                    $listsEnable = $configField['listsEnable'] ? true : false;
                }

                if (isset($configField['detailEnable'])) {
                    $detailEnable = $configField['detailEnable'] ? true : false;
                }

                if (isset($configField['createEnable'])) {
                    $createEnable = $configField['createEnable'] ? true : false;
                }

                if (isset($configField['editEnable'])) {
                    $editEnable = $configField['editEnable'] ? true : false;
                }
            }

            $formattedFields[$field->Field] = array(
                'name' => $name, // 字段名
                'field' => $field->Field, // 字段名
                'type' => $type, // 类型
                'typeLength' => $typeLength, // 类型长度
                'isNumber' => $isNumber,  // 是否数字
                'unsigned' => $unsigned, // 是否非负，数字类型时有效
                'default' => $default, // 默认值
                'extra' => $extra, // 附加内容
                'comment' => $comment, // 注释
                'optionType' => $optionType, // 枚举类型取值范围
                'optionData' => $optionData, // 枚举类型取值范围
                'format' => $format, // 格式化
                'disable' => $disable, // 是否禁用（不展示，不可编辑）
                'listsEnable' => $listsEnable, // 是否在列表页可用
                'detailEnable' => $detailEnable, // 是否在详情页可用
                'createEnable' => $createEnable, // 是否新建时可用
                'editEnable' => $editEnable, // 是否编辑时可用
            );
        }

        return $formattedFields;
    }

}
