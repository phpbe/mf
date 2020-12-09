<?php

namespace Be\Plugin\Curd;

use Be\Plugin\Detail\Item\DetailItemAvatar;
use Be\Plugin\Detail\Item\DetailItemCustom;
use Be\Plugin\Detail\Item\DetailItemImage;
use Be\Plugin\Detail\Item\DetailItemProgress;
use Be\Plugin\Detail\Item\DetailItemSwitch;
use Be\Plugin\Detail\Item\DetailItemText;
use Be\Plugin\Form\Item\FormItemDatePickerMonthRange;
use Be\Plugin\Form\Item\FormItemDatePickerRange;
use Be\Plugin\Form\Item\FormItemHidden;
use Be\Plugin\Form\Item\FormItemInput;
use Be\Plugin\Form\Item\FormItemTimePickerRange;
use Be\Plugin\Table\Item\TableItemAvatar;
use Be\Plugin\Table\Item\TableItemCustom;
use Be\Plugin\Table\Item\TableItemImage;
use Be\Plugin\Table\Item\TableItemProgress;
use Be\Plugin\Table\Item\TableItemSwitch;
use Be\System\Be;
use Be\System\Db\Table;
use Be\System\Exception\PluginException;
use Be\System\Plugin;
use Be\System\Request;
use Be\System\Response;

/**
 * 增删改查
 *
 * Class Curd
 * @package Be\Plugin
 */
class Curd extends Plugin
{

    /**
     * 配置项
     *
     * @param array $setting
     * @return Plugin
     */
    public function setting($setting = [])
    {
        if (!isset($setting['db'])) {
            $setting['db'] = 'master';
        }

        $this->setting = $setting;
        return $this;
    }

    /**
     * 执行指定任务
     *
     * @param string $task
     */
    public function execute($task = null)
    {
        if ($task === null) {
            $task = Request::request('task', 'lists');
        }

        if (method_exists($this, $task)) {
            $this->$task();
        }
    }

    /**
     * 列表展示
     *
     */
    public function lists()
    {
        if (Request::isAjax()) {
            try {
                $postData = Request::json();
                $formData = $postData['formData'];
                $table = $this->getTable($formData);

                $total = $table->count();

                $orderBy = isset($postData['orderBy']) ? $postData['orderBy'] : '';
                if ($orderBy) {
                    $orderByDir = isset($postData['orderByDir']) ? strtoupper($postData['orderByDir']) : '';
                    if (!in_array($orderByDir, ['ASC', 'DESC'])) {
                        $orderByDir = 'DESC';
                    }

                    $table->orderBy($orderBy, $orderByDir);
                } else {
                    if (isset($this->setting['lists']['orderBy'])) {
                        $orderBy = $this->setting['lists']['orderBy'];
                        if (isset($this->setting['lists']['orderByDir'])) {
                            $orderByDir = $this->setting['lists']['orderByDir'];
                            $table->orderBy($orderBy, $orderByDir);
                        } else {
                            $table->orderBy($orderBy);
                        }
                    }
                }

                $page = $postData['page'];
                $pageSize = $postData['pageSize'];
                $table->offset(($page - 1) * $pageSize)->limit($pageSize);

                $rows = $table->getArrays();

                $formattedRows = [];
                foreach ($rows as $row) {
                    $formattedRow = [];

                    foreach ($this->setting['lists']['table']['items'] as $item) {
                        if (!isset($item['name'])) {
                            continue;
                        }
                        $itemName = $item['name'];
                        $itemValue = '';
                        if (isset($item['value'])) {
                            $value = $item['value'];
                            if ($value instanceof \Closure) {
                                $itemValue = $value($row);
                            } else {
                                $itemValue = $value;
                            }
                        } else {
                            if (isset($row[$itemName])) {
                                $itemValue = $row[$itemName];
                            }
                        }

                        if (isset($item['keyValues'])) {
                            $keyValues = $item['keyValues'];
                            if ($keyValues instanceof \Closure) {
                                $itemValue = $keyValues($itemValue);
                            } else {
                                if (isset($keyValues[$itemValue])) {
                                    $itemValue = $keyValues[$itemValue];
                                } else {
                                    $itemValue = '';
                                }
                            }
                        }

                        $formattedRow[$itemName] = $itemValue;
                    }

                    foreach ($row as $k => $v) {
                        if (isset($formattedRow[$k])) {
                            continue;
                        }

                        if (isset($this->setting['lists']['table']['exclude']) &&
                            is_array($this->setting['lists']['table']['exclude']) &&
                            in_array($k, $this->setting['lists']['table']['exclude'])
                        ) {
                            continue;
                        }

                        $formattedRow[$k] = $v;
                    }
                    $formattedRows[] = $formattedRow;
                }

                Response::set('success', true);
                Response::set('data', [
                    'total' => $total,
                    'tableData' => $formattedRows,
                ]);
                Response::json();
            } catch (\Throwable $t) {
                Response::set('success', false);
                Response::set('message', $t->getMessage());
                Response::json();
            }

        } else {
            $setting = $this->setting['lists'];

            Be::getPlugin('Lists')
                ->setting($setting)
                ->display();

            Response::createHistory();
        }

    }

    /*
     * 导入
     *
     */
    public function import()
    {
        $importer = Be::getPlugin('Importer');

        if (Request::isAjax()) {

            $dbName = 'master';
            if (isset($config['db'])) {
                $dbName = $config['db'];
            }

            $db = Be::getDb($dbName);

            $db->startTransaction();
            try {

                if (!isset($this->setting['table'])) {
                    throw new PluginException('未设置要导入的表名！');
                }
                $tableName = $this->setting['table'];

                $rows = $importer->process();
                foreach ($rows as $row) {
                    $db->insert($tableName, $row);
                }

                $db->commit();
            } catch (\Exception $e) {
                $db->rollback();

                Response::error($e->getMessage());
            }

            Response::success('导入成功！');
        } else {
            $setting = $this->setting['import'];
            $title = isset($this->setting['title']) ? ($this->setting['title'] . ' - 导入') : '导入';
            Response::setTitle($title);
            $importer->setting($setting)->display();
        }
    }

    /**
     * 导入 - 下载模板
     */
    public function downloadTemplate()
    {
        $importer = Be::getPlugin('Importer');
        $setting = $this->setting['import'];
        $title = isset($this->setting['title']) ? ($this->setting['title'] . ' - 导入') : '导入';
        Response::setTitle($title);
        $importer->setting($setting)->downloadTemplate();
    }

    /*
     * 导出
     *
     */
    public function export()
    {
        try {
            $postData = Request::post('data', '', '');
            $postData = json_decode($postData, true);
            $formData = $postData['formData'];

            $table = $this->getTable($formData);
            $rows = $table->getYieldArrays();

            $exporter = Be::getPlugin('Exporter');

            $exportDriver = isset($postData['postData']['driver']) ? $postData['postData']['driver'] : 'csv';

            $filename = null;
            if (isset($this->setting['export']['title'])) {
                $filename = $this->setting['export']['title'];
            } elseif (isset($this->setting['lists']['title'])) {
                $filename = $this->setting['lists']['title'];
            }
            $filename .= '（' . date('YmdHis') . '）';
            $filename .= ($exportDriver == 'csv' ? '.csv' : '.xls');

            $exporter->setDriver($exportDriver)->setOutput('http', $filename);

            $fields = null;
            if (isset($this->setting['export']['items'])) {
                $fields = $this->setting['export']['items'];
            } else {
                $fields = $this->setting['lists']['table']['items'];
            }

            $headers = [];
            foreach ($fields as $item) {
                if (!isset($item['label'])) {
                    continue;
                }
                $driver = null;
                if (isset($item['driver'])) {
                    $driverName = $item['driver'];
                    $driver = new $driverName($item);
                } else {
                    $driver = new \Be\Plugin\Table\Item\TableItemText($item);
                }

                $headers[] = $driver->label;
            }
            $exporter->setHeaders($headers);

            foreach ($rows as $row) {
                $formattedRow = [];

                foreach ($fields as $item) {
                    if (!isset($item['label'])) {
                        continue;
                    }

                    $itemName = $item['name'];
                    $itemValue = '';
                    if (isset($item['exportValue'])) {
                        $value = $item['exportValue'];
                        if ($value instanceof \Closure) {
                            $itemValue = $value($row);
                        } else {
                            $itemValue = $value;
                        }
                    } else {
                        if (isset($item['value'])) {
                            $value = $item['value'];
                            if ($value instanceof \Closure) {
                                $itemValue = $value($row);
                            } else {
                                $itemValue = $value;
                            }
                        } else {
                            if (isset($row[$itemName])) {
                                $itemValue = $row[$itemName];
                            }
                        }

                        if (isset($item['keyValues'])) {
                            $keyValues = $item['keyValues'];
                            if ($keyValues instanceof \Closure) {
                                $itemValue = $keyValues($itemValue);
                            } else {
                                if (isset($keyValues[$itemValue])) {
                                    $itemValue = $keyValues[$itemValue];
                                } else {
                                    $itemValue = '';
                                }
                            }
                        }
                    }

                    $formattedRow[$itemName] = $itemValue;
                }

                $exporter->addRow($formattedRow);
            }

            $exporter->end();

            $content = null;
            if (isset($this->setting['export']['title'])) {
                $content = $this->setting['export']['title'] . '（' . $exportDriver . '）';
            } elseif (isset($this->setting['lists']['title'])) {
                $content = '导出 ' . $this->setting['lists']['title'] . '（' . $exportDriver . '）';
            } else {
                $content = '导出 ' . $exportDriver;
            }

            if (!isset($this->setting['opLog']) || $this->setting['opLog']) {
                beOpLog($content, $formData);
            }

        } catch (\Throwable $t) {
            Response::error($t->getMessage());
        }
    }

    /**
     * 获取Table
     *
     * @param array $formData 查询条件
     * @return Table
     */
    private function getTable($formData)
    {
        $db = Be::getDb($this->setting['db']);
        $table = Be::newTable($this->setting['table'], $this->setting['db']);

        if (isset($this->setting['lists']['filter']) && count($this->setting['lists']['filter']) > 0) {
            foreach ($this->setting['lists']['filter'] as $filter) {
                $table->where($filter);
            }
        }

        if (isset($this->setting['lists']['tab'])) {
            if (isset($item['buildSql']) && $item['buildSql'] instanceof \Closure) {
                $buildSql = $item['buildSql'];
                $sql = $buildSql($this->setting['db'], $formData);
                if ($sql) {
                    $table->where($sql);
                }
            } else {
                $driver = new \Be\Plugin\Tab\Driver($this->setting['lists']['tab']);
                $driver->submit($formData);
                if ($driver->newValue !== '') {
                    $sql = $db->quoteKey($driver->name) . ' = ' . $db->quoteValue($driver->newValue);
                    $table->where($sql);
                }
            }
        }

        if (isset($this->setting['lists']['form']['items']) && count($this->setting['lists']['form']['items']) > 0) {
            foreach ($this->setting['lists']['form']['items'] as $item) {

                $driverName = null;
                if (isset($item['driver'])) {
                    $driverName = $item['driver'];
                } else {
                    $driverName = \Be\Plugin\Form\Item\FormItemInput::class;
                }

                $driver = new $driverName($item);

                if ($driver->name == null) {
                    continue;
                }

                $driver->submit($formData);

                if ($driver->newValue === '') {
                    continue;
                }

                if (isset($item['buildSql']) && $item['buildSql'] instanceof \Closure) {
                    $buildSql = $item['buildSql'];
                    $sql = $buildSql($this->setting['db'], $formData);
                    if ($sql) {
                        $table->where($sql);
                    }
                } else {

                    $op = null;
                    if (isset($item['op'])) {
                        $op = strtoupper($item['op']);
                    } else {
                        switch ($driverName) {
                            case FormItemDatePickerMonthRange::class:
                            case FormItemDatePickerRange::class:
                            case FormItemTimePickerRange::class:
                                $op = 'RANGE';
                                break;
                            case FormItemInput::class:
                                $op = '%LIKE%';
                                break;
                            default:
                                $op = '=';
                        }
                    }

                    $sql = null;
                    switch ($op) {
                        case 'LIKE':
                            $sql = $db->quoteKey($driver->name) . ' LIKE ' . $db->quoteValue($driver->newValue);
                            break;
                        case '%LIKE%':
                            $sql = $db->quoteKey($driver->name) . ' LIKE ' . $db->quoteValue('%' . $driver->newValue . '%');
                            break;
                        case 'LIKE%':
                            $sql = $db->quoteKey($driver->name) . ' LIKE ' . $db->quoteValue($driver->newValue . '%');
                            break;
                        case '%LIKE':
                            $sql = $db->quoteKey($driver->name) . ' LIKE ' . $db->quoteValue('%' . $driver->newValue);
                            break;
                        case 'RANGE':
                            if (is_array($driver->newValue) && count($driver->newValue) == 2) {
                                $sql = $db->quoteKey($driver->name) . ' >= ' . $db->quoteValue($driver->newValue[0]);
                                $sql .= ' AND ';
                                $sql .= $db->quoteKey($driver->name) . ' < ' . $db->quoteValue($driver->newValue[1]);
                            }
                            break;
                        case 'BETWEEN':
                            if (is_array($driver->newValue) && count($driver->newValue) == 2) {
                                $sql = $db->quoteKey($driver->name) . ' BETWEEN ' . $db->quoteValue($driver->newValue[0]);
                                $sql .= ' AND ';
                                $sql .= $db->quoteValue($driver->newValue[1]);
                            }
                            break;
                        case 'IN':
                            if (is_array($driver->newValue)) {
                                $newValue = [];
                                foreach ($driver->newValue as $x) {
                                    $newValue[] = $db->quoteValue($x);
                                }
                                $sql = $db->quoteKey($driver->name) . ' IN (' . implode(',', $newValue) . ')';
                            }
                            break;
                        default:
                            $sql = $db->quoteKey($driver->name) . ' = ' . $db->quoteValue($driver->newValue);
                            break;
                    }

                    if ($sql) {
                        $table->where($sql);
                    }
                }
            }
        }
        return $table;
    }

    /**
     * 明细
     *
     * @param array $setting 配置项
     */
    public function detail()
    {
        $postData = Request::post('data', '', '');
        $postData = json_decode($postData, true);

        $tuple = Be::newTuple($this->setting['table'], $this->setting['db']);

        try {

            $primaryKey = $tuple->getPrimaryKey();

            $primaryKeyValue = null;
            if (is_array($primaryKey)) {
                $primaryKeyValue = [];
                foreach ($primaryKey as $pKey) {
                    if (!isset($postData['row'][$pKey])) {
                        throw new PluginException('主键（row.' . $pKey . '）缺失！');
                    }

                    $primaryKeyValue[$pKey] = $postData['row'][$pKey];
                }
            } else {
                if (!isset($postData['row'][$primaryKey])) {
                    throw new PluginException('主键（row.' . $primaryKey . '）缺失！');
                }

                $primaryKeyValue = $postData['row'][$primaryKey];
            }

            $tuple->load($primaryKeyValue);
            $row = $tuple->toArray();

            $fields = null;
            if (isset($this->setting['detail']['form']['items'])) {
                $fields = $this->setting['detail']['form']['items'];
            } else {
                $fields = [];

                $listFields = $this->setting['lists']['form']['items'];
                foreach ($listFields as &$item) {
                    if (!isset($item['label'])) {
                        continue;
                    }

                    if (isset($item['driver'])) {
                        switch ($item['driver']) {
                            case TableItemAvatar::class:
                                $item['driver'] = DetailItemAvatar::class;
                                break;
                            case TableItemCustom::class:
                                $item['driver'] = DetailItemCustom::class;
                                break;
                            case TableItemImage::class:
                                $item['driver'] = DetailItemImage::class;
                                break;
                            case TableItemProgress::class:
                                $item['driver'] = DetailItemProgress::class;
                                break;
                            case TableItemSwitch::class:
                                $item['driver'] = DetailItemSwitch::class;
                                break;
                            default:
                                $item['driver'] = DetailItemText::class;
                                break;
                        }
                    }

                    $fields[] = $item;
                }
                unset($item);
            }

            $setting = $this->setting['detail'];
            $setting['form']['items'] = $fields;

            Be::getPlugin('Detail')
                ->setting($setting)
                ->setValue($row)
                ->display();

        } catch (\Throwable $t) {
            Response::error($t->getMessage());
        }
    }

    /**
     * 创建
     *
     */
    public function create()
    {
        $title = isset($this->setting['create']['title']) ? $this->setting['create']['title'] : '新建';

        if (Request::isAjax()) {

            $db = Be::getDb($this->setting['db']);
            $db->startTransaction();
            try {

                $postData = Request::json();
                $formData = $postData['formData'];

                $tuple = Be::newTuple($this->setting['table'], $this->setting['db']);

                if (isset($this->setting['create']['events']['before'])) {
                    $this->on('before', $this->setting['create']['events']['before']);
                }

                if (isset($this->setting['create']['events']['after'])) {
                    $this->on('after', $this->setting['create']['events']['after']);
                }

                if (isset($this->setting['create']['form']['items']) && count($this->setting['create']['form']['items']) > 0) {
                    foreach ($this->setting['create']['form']['items'] as $item) {
                        $driver = null;
                        if (isset($item['driver'])) {
                            $driverName = $item['driver'];
                            $driver = new $driverName($item);
                        } else {
                            $driver = new \Be\Plugin\Form\Item\FormItemInput($item);
                        }

                        if ($driver->name == null) {
                            continue;
                        }

                        $driver->submit($formData);
                        $name = $driver->name;

                        // 必填字段
                        if ($driver->required) {
                            if ($driver->newValue === '') {
                                throw new PluginException($driver->label . ' 缺失！');
                            }
                        }

                        // 检查唯一性
                        if (isset($item['unique']) && $item['unique']) {
                            $sql = 'SELECT COUNT(*) FROM ' . $db->quoteKey($this->setting['table']) . ' WHERE ' . $db->quoteKey($name) . '=' . $db->quoteValue($driver->newValue);
                            if ($db->getValue($sql) > 0) {
                                throw new PluginException($driver->label . ' 已存在 ' . $driver->newValue . ' 的记录！');
                            }
                        }

                        $tuple->$name = $driver->newValue;
                    }
                }

                $this->trigger('before', $tuple);
                $tuple->save();
                $this->trigger('after', $tuple);

                $primaryKey = $tuple->getPrimaryKey();

                $strPrimaryKey = null;
                $strPrimaryKeyValue = null;
                if (is_array($primaryKey)) {
                    $strPrimaryKey = '（' . implode(',', $primaryKey) . '）';
                    $primaryKeyValue = [];
                    foreach ($primaryKey as $pKey) {
                        $primaryKeyValue[] = $tuple->$pKey;
                    }
                    $strPrimaryKeyValue = '（' . implode(',', $primaryKeyValue) . '）';
                } else {
                    $strPrimaryKey = $primaryKey;
                    $strPrimaryKeyValue = $tuple->$primaryKey;
                }

                if (!isset($this->setting['opLog']) || $this->setting['opLog']) {
                    beOpLog($title . '：新建' . $strPrimaryKey . '为' . $strPrimaryKeyValue . '的记录！', $formData);
                }
                $db->commit();
                Response::success($title . '：新建成功！');

            } catch (\Throwable $t) {
                $db->rollback();
                Response::error($t->getMessage());
            }

        } else {
            Response::setTitle($title);

            $setting = $this->setting['create'];
            Be::getPlugin('Form')->setting($setting)->display();
        }
    }

    /**
     * 编辑
     *
     */
    public function edit()
    {
        $title = isset($this->setting['edit']['title']) ? $this->setting['edit']['title'] : '编辑';

        $tuple = Be::newTuple($this->setting['table'], $this->setting['db']);

        if (Request::isAjax()) {

            $db = Be::getDb($this->setting['db']);
            $db->startTransaction();
            try {

                $postData = Request::json();
                $formData = $postData['formData'];

                $primaryKey = $tuple->getPrimaryKey();
                $primaryKeyValue = null;
                if (is_array($primaryKey)) {
                    $primaryKeyValue = [];
                    foreach ($primaryKey as $pKey) {
                        if (!isset($formData[$pKey])) {
                            Response::error('主键（' . $pKey . '）缺失！');
                        }

                        $primaryKeyValue[$pKey] = $formData[$pKey];
                    }
                } else {
                    if (!isset($formData[$primaryKey])) {
                        Response::error('主键（' . $primaryKey . '）缺失！');
                    }

                    $primaryKeyValue = $formData[$primaryKey];
                }
                $tuple->load($primaryKeyValue);

                if (isset($this->setting['edit']['events']['before'])) {
                    $this->on('before', $this->setting['edit']['events']['before']);
                }

                if (isset($this->setting['edit']['events']['after'])) {
                    $this->on('after', $this->setting['create']['events']['after']);
                }

                if (isset($this->setting['edit']['form']['items']) && count($this->setting['edit']['form']['items']) > 0) {
                    foreach ($this->setting['edit']['form']['items'] as $item) {

                        // 禁止编辑字段
                        if (isset($item['disabled']) && $item['disabled']) {
                            continue;
                        }

                        $driver = null;
                        if (isset($item['driver'])) {
                            $driverName = $item['driver'];
                            $driver = new $driverName($item);
                        } else {
                            $driver = new \Be\Plugin\Form\Item\FormItemInput($item);
                        }

                        if ($driver->name == null) {
                            continue;
                        }

                        $driver->submit($formData);
                        $name = $driver->name;

                        // 必填字段
                        if ($driver->required) {
                            if ($driver->newValue === '') {
                                throw new PluginException($driver->label . ' 缺失！');
                            }
                        }

                        // 检查唯一性
                        if (isset($item['unique']) && $item['unique']) {
                            $sql = 'SELECT COUNT(*) FROM ' . $db->quoteKey($this->setting['table']) . ' WHERE ' . $db->quoteKey($name) . '=' . $db->quoteValue($driver->newValue);
                            if (is_array($primaryKey)) {
                                foreach ($primaryKey as $pKey) {
                                    $sql .= ' AND ' . $db->quoteKey($pKey) . '!=' . $db->quoteValue($formData[$pKey]);
                                }
                            } else {
                                $sql .= ' AND ' . $db->quoteKey($primaryKey) . '!=' . $db->quoteValue($formData[$primaryKey]);
                            }

                            if ($db->getValue($sql) > 0) {
                                throw new PluginException($driver->label . ' 已存在 ' . $driver->newValue . ' 的记录！');
                            }
                        }

                        $tuple->$name = $driver->newValue;
                    }
                }

                $this->trigger('before', $tuple);
                $tuple->save();
                $this->trigger('after', $tuple);

                $strPrimaryKey = null;
                $strPrimaryKeyValue = null;
                if (is_array($primaryKey)) {
                    $strPrimaryKey = '（' . implode(',', $primaryKey) . '）';
                    $strPrimaryKeyValue = '（' . implode(',', array_values($primaryKeyValue)) . '）';
                } else {
                    $strPrimaryKey = $primaryKey;
                    $strPrimaryKeyValue = $tuple->$primaryKey;
                }

                if (!isset($this->setting['opLog']) || $this->setting['opLog']) {
                    beOpLog($title . '：编辑' . $strPrimaryKey . '为' . $strPrimaryKeyValue . '的记录！', $formData);
                }
                $db->commit();
                Response::success($title . '：编辑成功！');
            } catch (\Throwable $t) {
                $db->rollback();
                Response::error($t->getMessage());
            }

        } else {

            try {

                $postData = Request::post('data', '', '');
                $postData = json_decode($postData, true);

                $primaryKey = $tuple->getPrimaryKey();
                $primaryKeyValue = null;
                if (is_array($primaryKey)) {
                    $primaryKeyValue = [];
                    foreach ($primaryKey as $pKey) {
                        if (!isset($postData['row'][$pKey])) {
                            throw new PluginException('主键（row.' . $pKey . '）缺失！');
                        }

                        $primaryKeyValue[$pKey] = $postData['row'][$pKey];
                    }
                } else {
                    if (!isset($postData['row'][$primaryKey])) {
                        throw new PluginException('主键（row.' . $primaryKey . '）缺失！');
                    }

                    $primaryKeyValue = $postData['row'][$primaryKey];
                }

                $tuple->load($primaryKeyValue);

                $setting = $this->setting['edit'];

                if (is_array($primaryKeyValue)) {
                    foreach ($primaryKeyValue as $pKey => $pVal) {
                        $setting['form']['items'][] = [
                            'name' => $pKey,
                            'value' => $pVal,
                            'driver' => FormItemHidden::class,
                        ];
                    }
                } else {
                    $setting['form']['items'][] = [
                        'name' => $primaryKey,
                        'value' => $primaryKeyValue,
                        'driver' => FormItemHidden::class,
                    ];
                }

                Response::setTitle($title);
                Be::getPlugin('Form')
                    ->setting($setting)
                    ->setValue($tuple->toArray())
                    ->display();

            } catch (\Throwable $t) {
                Response::error($t->getMessage());
            }
        }
    }

    /**
     * 编辑某个字段的值
     */
    public function fieldEdit()
    {
        if (isset($this->setting['fieldEdit']['events']['before'])) {
            $this->on('before', $this->setting['fieldEdit']['events']['before']);
        }

        if (isset($this->setting['fieldEdit']['events']['after'])) {
            $this->on('after', $this->setting['fieldEdit']['events']['after']);
        }

        $postData = Request::json();
        if (!isset($postData['postData']['field'])) {
            Response::error('参数（postData.field）缺失！');
        }
        $field = $postData['postData']['field'];

        $fieldLabel = '';
        if (isset($this->setting['lists']['field']['items'])) {
            foreach ($this->setting['lists']['field']['items'] as $fieldItem) {
                if ($fieldItem['name'] == $field) {
                    $fieldLabel = $fieldItem['label'];
                    break;
                }
            }
        }

        $title = null;

        $db = Be::getDb($this->setting['db']);

        if (isset($postData['selectedRows'])) {
            if (!is_array($postData['selectedRows']) || count($postData['selectedRows']) == 0) {
                Response::error('你尚未选择要操作的数据！');
            }

            if (!isset($postData['postData']['value'])) {
                Response::error('参数（postData.value）缺失！');
            }
            $value = $postData['postData']['value'];

            $title = '修改字段 ' . $fieldLabel . '（' . $field . '）的值为' . $value;
            if (isset($this->setting['fieldEdit']['title'])) {
                $title = $this->setting['fieldEdit']['title'] . '（' . $title . '）';
            }

            $db->startTransaction();
            try {
                $strPrimaryKey = null;
                $primaryKeyValues = [];

                $i = 0;
                foreach ($postData['selectedRows'] as $row) {
                    $tuple = Be::newTuple($this->setting['table'], $this->setting['db']);
                    $primaryKey = $tuple->getPrimaryKey();

                    $primaryKeyValue = null;
                    if (is_array($primaryKey)) {
                        $primaryKeyValue = [];
                        foreach ($primaryKey as $pKey) {
                            if (!isset($row[$pKey])) {
                                Response::error('主键（selectedRows[' . $i . '].' . $pKey . '）缺失！');
                            }

                            $primaryKeyValue[$pKey] = $row[$pKey];
                        }
                    } else {
                        if (!isset($row[$primaryKey])) {
                            Response::error('主键（selectedRows[' . $i . '].' . $primaryKey . '）缺失！');
                        }

                        $primaryKeyValue = $row[$primaryKey];
                    }

                    $tuple->load($primaryKeyValue);
                    $tuple->$field = $value;
                    $this->trigger('before', $tuple);
                    $tuple->save();
                    $this->trigger('after', $tuple);

                    if ($strPrimaryKey === null) {
                        if (is_array($primaryKey)) {
                            $strPrimaryKey = '（' . implode(',', $primaryKey) . '）';
                        } else {
                            $strPrimaryKey = $primaryKey;
                        }
                    }

                    if (is_array($primaryKeyValue)) {
                        $primaryKeyValues[] = '（' . implode(',', $primaryKeyValue) . '）';
                    } else {
                        $primaryKeyValues[] = $primaryKeyValue;
                    }

                    $i++;
                }

                $strPrimaryKeyValue = implode(',', $primaryKeyValues);

                if (!isset($this->setting['opLog']) || $this->setting['opLog']) {
                    beOpLog($title . '（#' . $strPrimaryKey . '：' . $strPrimaryKeyValue . '）');
                }
                $db->commit();
            } catch (\Throwable $t) {

                $db->rollback();
                Response::error($t->getMessage());
            }

        } elseif (isset($postData['row'])) {

            if (!isset($postData['row'][$field])) {
                Response::error('参数（row.' . $field . '）缺失！');
            }

            $value = isset($postData['postData']['value']) ? $postData['postData']['value'] : $postData['row'][$field];

            $title = '修改字段 ' . $fieldLabel . '（' . $field . '）的值为' . $value;
            if (isset($this->setting['fieldEdit']['title'])) {
                $title = $this->setting['fieldEdit']['title'] . '（' . $title . '）';
            }

            $db->startTransaction();
            try {
                $tuple = Be::newTuple($this->setting['table'], $this->setting['db']);
                $primaryKey = $tuple->getPrimaryKey();

                $primaryKeyValue = null;
                if (is_array($primaryKey)) {
                    $primaryKeyValue = [];
                    foreach ($primaryKey as $pKey) {
                        if (!isset($postData['row'][$pKey])) {
                            Response::error('主键（row.' . $pKey . '）缺失！');
                        }

                        $primaryKeyValue[$pKey] = $postData['row'][$pKey];
                    }
                } else {
                    if (!isset($postData['row'][$primaryKey])) {
                        Response::error('主键（row.' . $primaryKey . '）缺失！');
                    }

                    $primaryKeyValue = $postData['row'][$primaryKey];
                }
                $tuple->load($primaryKeyValue);
                $tuple->$field = $value;
                $this->trigger('before', $tuple);
                $tuple->save();
                $this->trigger('after', $tuple);

                $strPrimaryKey = null;
                $strPrimaryKeyValue = null;
                if (is_array($primaryKey)) {
                    $strPrimaryKey = '（' . implode(',', $primaryKey) . '）';
                    $strPrimaryKeyValue = '（' . implode(',', $primaryKeyValue) . '）';
                } else {
                    $strPrimaryKey = $primaryKey;
                    $strPrimaryKeyValue = $primaryKeyValue;
                }

                if (!isset($this->setting['opLog']) || $this->setting['opLog']) {
                    beOpLog($title . '（#' . $strPrimaryKey . '：' . $strPrimaryKeyValue . '）');
                }
                $db->commit();
                Response::success($title . '，执行成功！');
            } catch (\Throwable $t) {
                $db->rollback();
                Response::error($t->getMessage());
            }
        } else {
            Response::error('参数（rows或row）缺失！');
        }
    }

    /**
     * 删除
     *
     */
    public function delete()
    {
        $postData = Request::json();

        $title = null;
        if (isset($this->setting['delete']['title'])) {
            $title = $this->setting['delete']['title'];
        } else {
            $title = '删除记录';
        }

        $db = Be::getDb($this->setting['db']);

        if (isset($postData['selectedRows'])) {

            if (!is_array($postData['selectedRows']) || count($postData['selectedRows']) == 0) {
                Response::error('你尚未选择要操作的数据！');
            }

            $db->startTransaction();
            try {
                $strPrimaryKey = null;
                $primaryKeyValues = [];

                $i = 0;
                foreach ($postData['selectedRows'] as $row) {
                    $tuple = Be::newTuple($this->setting['table'], $this->setting['db']);
                    $primaryKey = $tuple->getPrimaryKey();

                    $primaryKeyValue = null;
                    if (is_array($primaryKey)) {
                        $primaryKeyValue = [];
                        foreach ($primaryKey as $pKey) {
                            if (!isset($row[$pKey])) {
                                Response::error('主键（selectedRows[' . $i . '].' . $pKey . '）缺失！');
                            }

                            $primaryKeyValue[$pKey] = $row[$pKey];
                        }
                    } else {
                        if (!isset($row[$primaryKey])) {
                            Response::error('主键（selectedRows[' . $i . '].' . $primaryKey . '）缺失！');
                        }

                        $primaryKeyValue = $row[$primaryKey];
                    }

                    $tuple->load($primaryKeyValue);
                    $this->trigger('before', $tuple);
                    $tuple->delete();
                    $this->trigger('after', $tuple);

                    if ($strPrimaryKey === null) {
                        if (is_array($primaryKey)) {
                            $strPrimaryKey = '（' . implode(',', $primaryKey) . '）';
                        } else {
                            $strPrimaryKey = $primaryKey;
                        }
                    }

                    if (is_array($primaryKeyValue)) {
                        $primaryKeyValues[] = '（' . implode(',', $primaryKeyValue) . '）';
                    } else {
                        $primaryKeyValues[] = $primaryKeyValue;
                    }

                    $i++;
                }

                $strPrimaryKeyValue = implode(',', $primaryKeyValues);

                if (!isset($this->setting['opLog']) || $this->setting['opLog']) {
                    beOpLog($title . '（#' . $strPrimaryKey . '：' . $strPrimaryKeyValue . '）');
                }
                $db->commit();
                Response::success($title . '，执行成功！');
            } catch (\Throwable $t) {
                $db->rollback();
                Response::error($t->getMessage());
            }

        } elseif (isset($postData['row'])) {
            $db->startTransaction();
            try {
                $tuple = Be::newTuple($this->setting['table'], $this->setting['db']);
                $primaryKey = $tuple->getPrimaryKey();

                $primaryKeyValue = null;
                if (is_array($primaryKey)) {
                    $primaryKeyValue = [];
                    foreach ($primaryKey as $pKey) {
                        if (!isset($postData['row'][$pKey])) {
                            Response::error('主键（row.' . $pKey . '）缺失！');
                        }

                        $primaryKeyValue[$pKey] = $postData['row'][$pKey];
                    }
                } else {
                    if (!isset($postData['row'][$primaryKey])) {
                        Response::error('主键（row.' . $primaryKey . '）缺失！');
                    }

                    $primaryKeyValue = $postData['row'][$primaryKey];
                }
                $tuple->load($primaryKeyValue);
                $this->trigger('before', $tuple);
                $tuple->delete();
                $this->trigger('after', $tuple);

                $strPrimaryKey = null;
                $strPrimaryKeyValue = null;
                if (is_array($primaryKey)) {
                    $strPrimaryKey = '（' . implode(',', $primaryKey) . '）';
                    $strPrimaryKeyValue = '（' . implode(',', $primaryKeyValue) . '）';
                } else {
                    $strPrimaryKey = $primaryKey;
                    $strPrimaryKeyValue = $primaryKeyValue;
                }

                if (!isset($this->setting['opLog']) || $this->setting['opLog']) {
                    beOpLog($title . '（#' . $strPrimaryKey . '：' . $strPrimaryKeyValue . '）');
                }
                $db->commit();
                Response::success($title . '，执行成功！');
            } catch (\Throwable $t) {
                $db->rollback();
                Response::error($t->getMessage());
            }
        }
    }


}

