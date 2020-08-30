<?php

namespace Be\Plugin\Curd;

use Be\Plugin\Curd\FieldItem\FieldItemAvatar;
use Be\Plugin\Curd\FieldItem\FieldItemCustom;
use Be\Plugin\Curd\FieldItem\FieldItemImage;
use Be\Plugin\Curd\FieldItem\FieldItemProgress;
use Be\Plugin\Curd\FieldItem\FieldItemSelection;
use Be\Plugin\Curd\FieldItem\FieldItemSwitch;
use Be\Plugin\Curd\FieldItem\FieldItemText;
use Be\Plugin\Detail\Item\DetailItemAvatar;
use Be\Plugin\Detail\Item\DetailItemCustom;
use Be\Plugin\Detail\Item\DetailItemImage;
use Be\Plugin\Detail\Item\DetailItemProgress;
use Be\Plugin\Detail\Item\DetailItemSwitch;
use Be\Plugin\Detail\Item\DetailItemText;
use Be\System\Be;
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
        $table = Be::newTable($this->setting['table'], $this->setting['db']);
        if (Request::isAjax()) {

            try {
                if (isset($this->setting['lists']['filter']) && count($this->setting['lists']['filter']) > 0) {
                    foreach ($this->setting['lists']['filter'] as $filter) {
                        $table->where($filter);
                    }
                }

                $postData = Request::json();
                $formData = $postData['formData'];
                if (isset($this->setting['lists']['tab'])) {
                    if (isset($item['buildSql']) && $item['buildSql'] instanceof \Closure) {
                        $buildSql = $item['buildSql'];
                        $sql = $buildSql($this->setting['db'], $formData);
                        if ($sql) {
                            $table->where($sql);
                        }
                    } else {
                        $driver = new \Be\Plugin\Curd\Tab($this->setting['lists']['tab']);
                        $driver->submit($formData);
                        $sql = $driver->buildSql($this->setting['db']);
                        if ($sql) {
                            $table->where($sql);
                        }
                    }
                }

                if (isset($this->setting['lists']['search']['items']) && count($this->setting['lists']['search']['items']) > 0) {
                    foreach ($this->setting['lists']['search']['items'] as $item) {

                        if (isset($item['buildSql']) && $item['buildSql'] instanceof \Closure) {
                            $buildSql = $item['buildSql'];
                            $sql = $buildSql($this->setting['db'], $formData);
                            if ($sql) {
                                $table->where($sql);
                            }
                        } else {
                            $driver = null;
                            if (isset($item['driver'])) {
                                $driverName = $item['driver'];
                                $driver = new $driverName($item);
                            } else {
                                $driver = new \Be\Plugin\Curd\SearchItem\SearchItemInput($item);
                            }
                            $driver->submit($formData);
                            $sql = $driver->buildSql($this->setting['db']);
                            if ($sql) {
                                $table->where($sql);
                            }
                        }
                    }
                }

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

                    foreach ($this->setting['lists']['field']['items'] as $item) {
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

                        if (isset($this->setting['lists']['field']['exclude']) &&
                            is_array($this->setting['lists']['field']['exclude']) &&
                            in_array($k, $this->setting['lists']['field']['exclude'])
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
                    'rows' => $formattedRows,
                ]);
                Response::ajax();
            } catch (\Exception $e) {
                Response::set('success', false);
                Response::set('message', $e->getMessage());
                Response::ajax();
            }

        } else {

            $pageSize = null;
            if (isset($this->setting['lists']['pageSize']) &&
                is_numeric($this->setting['lists']['pageSize']) &&
                $this->setting['lists']['pageSize'] > 0
            ) {
                $pageSize = $this->setting['lists']['pageSize'];
            } else {
                $pageSize = Be::getConfig('System.System')->pageSize;;
            }

            Response::setTitle($this->setting['lists']['title']);

            Response::set('url', Request::url());
            Response::set('setting', $this->setting);
            Response::set('table', $table);
            Response::set('pageSize', $pageSize);

            $theme = null;
            if (isset($this->setting['lists']['theme'])) {
                $theme = $this->setting['lists']['theme'];
            }
            Response::display('Plugin.Curd.lists', $theme);
            Response::createHistory();
        }

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
            if (isset($this->setting['detail']['field']['items'])) {
                $fields = $this->setting['detail']['field']['items'];
            } else {
                $fields = [];

                $listFields = $this->setting['lists']['field']['items'];
                foreach ($listFields as &$item) {
                    if (!isset($item['label'])) {
                        continue;
                    }

                    if (isset($item['driver'])) {
                        switch ($item['driver']) {
                            case FieldItemAvatar::class:
                                $item['driver'] = DetailItemAvatar::class;
                                break;
                            case FieldItemCustom::class:
                                $item['driver'] = DetailItemCustom::class;
                                break;
                            case FieldItemImage::class:
                                $item['driver'] = DetailItemImage::class;
                                break;
                            case FieldItemProgress::class:
                                $item['driver'] = DetailItemProgress::class;
                                break;
                            case FieldItemSwitch::class:
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

            foreach ($fields as &$item) {
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

                $item['value'] = $itemValue;
            }
            unset($item);

            $setting = $this->setting['detail'];
            $setting['field']['items'] = $fields;

            Be::getPlugin('Detail')->setting($setting)->display();

        } catch (\Exception $e) {
            Response::error($e->getMessage());
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

            $postData = Request::json();
            $formData = $postData['formData'];

            $tuple = Be::newTuple($this->setting['table'], $this->setting['db']);

            if (isset($this->setting['create']['events']['BeforeCreate'])) {
                $this->on('BeforeEdit', $this->setting['create']['events']['BeforeCreate']);
            }

            if (isset($this->setting['create']['events']['AfterCreate'])) {
                $this->on('AfterCreate', $this->setting['create']['events']['AfterCreate']);
            }

            $db = Be::getDb($this->setting['db']);
            $db->startTransaction();
            try {

                if (isset($this->setting['create']['form']['items']) && count($this->setting['create']['form']['items']) > 0) {
                    foreach ($this->setting['create']['form']['items'] as $item) {
                        $driver = null;
                        if (isset($item['driver'])) {
                            $driverName = $item['driver'];
                            $driver = new $driverName($item);
                        } else {
                            $driver = new \Be\Plugin\Form\Item\FormItemInput($item);
                        }
                        $driver->submit($formData);
                        $name = $driver->name;

                        // 必填字段
                        if ($driver->required) {
                            if ($driver->newValue == '') {
                                throw new PluginException($driver->label . ' 缺失！');
                            }
                        }

                        // 检查唯一性
                        if (isset($item['unique']) && $item['unique']) {
                            if (Be::getTable($this->setting['table'], $this->setting['db'])
                                ->where($name, $driver->value)
                                ->count() > 0) {
                                throw new PluginException($driver->label . ' 已存在 '.$driver->value.' 的记录！');
                            }
                        }

                        $tuple->$name = $driver->newValue;
                    }
                }

                $this->trigger('BeforeCreate', $tuple);
                $tuple->save();
                $this->trigger('AfterCreate', $tuple);

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

                beSystemLog($title . '：新建' . $strPrimaryKey . '为' . $strPrimaryKeyValue . '的记录！', $formData);

                $db->commit();
            } catch (\Exception $e) {
                $db->rollback();
                Response::error($e->getMessage());
            }

            Response::success('新建成功！');

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
        if (Request::isAjax()) {

            $tuple = Be::newTuple($this->setting['table']);

            $primaryKey = $tuple->getPrimaryKey();
            $primaryKeyValue = Request::get($primaryKey, null);

            if (!$primaryKeyValue) {
                Response::error('参数（' . $primaryKey . '）缺失！');
            }

            $tuple->load($primaryKeyValue);
            if (!$tuple->$primaryKey) {
                Response::error('主键编号（' . $primaryKey . '）为 ' . $primaryKeyValue . ' 的记录不存在！');
            }

            if (isset($this->setting['edit']['events']['BeforeEdit'])) {
                $this->on('BeforeEdit', $this->setting['edit']['events']['BeforeEdit']);
            }

            if (isset($this->setting['edit']['events']['AfterEdit'])) {
                $this->on('AfterEdit', $this->setting['edit']['events']['AfterEdit']);
            }

            $db = Be::getDb($this->setting['db']);
            $db->startTransaction();
            try {

                $tuple->bind(Request::post());
                $this->trigger('BeforeEdit', $tuple);
                $tuple->save();
                $this->trigger('AfterEdit', $tuple);

                beSystemLog($setting['title'] . '：编辑' . $primaryKey . '为' . $primaryKeyValue . '的记录！');

                $db->commit();
            } catch (\Exception $e) {

                $db->rollback();
                Response::error($e->getMessage());
            }

            Response::success('修改成功！');
        } else {
            $title = isset($this->setting['edit']['title']) ? $this->setting['edit']['title'] : '编辑';
            Response::setTitle($title);

            $setting = $this->setting['edit'];

            Be::getPlugin('Form')->setting($setting)->display();
        }
    }

    /**
     * 编辑某个字段的值
     */
    public function fieldEdit()
    {
        if (isset($this->setting['fieldEdit']['events']['BeforeFieldEdit'])) {
            $this->on('BeforeFieldEdit', $this->setting['fieldEdit']['events']['BeforeFieldEdit']);
        }

        if (isset($this->setting['fieldEdit']['events']['AfterFieldEdit'])) {
            $this->on('AfterFieldEdit', $this->setting['fieldEdit']['events']['AfterFieldEdit']);
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

            Be::getDb()->startTransaction();
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
                    $this->trigger('BeforeFieldEdit', $tuple);
                    $tuple->save();
                    $this->trigger('AfterFieldEdit', $tuple);

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

                beSystemLog($title . '（#' . $strPrimaryKey . '：' . $strPrimaryKeyValue . '）');

                Be::getDb()->commit();
            } catch (\Exception $e) {

                Be::getDb()->rollback();
                Response::error($e->getMessage());
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
                $this->trigger('BeforeFieldEdit', $tuple);
                $tuple->save();
                $this->trigger('AfterFieldEdit', $tuple);

                $strPrimaryKey = null;
                $strPrimaryKeyValue = null;
                if (is_array($primaryKey)) {
                    $strPrimaryKey = '（' . implode(',', $primaryKey) . '）';
                    $strPrimaryKeyValue = '（' . implode(',', $primaryKeyValue) . '）';
                } else {
                    $strPrimaryKey = $primaryKey;
                    $strPrimaryKeyValue = $primaryKeyValue;
                }

                beSystemLog($title . '（#' . $strPrimaryKey . '：' . $strPrimaryKeyValue . '）');

                $db->commit();
            } catch (\Exception $e) {
                $db->rollback();
                Response::error($e->getMessage());
            }
        } else {
            Response::error('参数（rows或row）缺失！');
        }

        Response::success($title . '，执行成功！');
    }

    /**
     * 删除
     *
     */
    public function delete()
    {
        $setting = $this->setting['delete'];

        $tuple = Be::newTuple($setting['table']);

        $primaryKey = $tuple->getPrimaryKey();
        $primaryKeyValue = Request::get($primaryKey, null);

        if (!$primaryKeyValue) {
            Response::error('参数（' . $primaryKey . '）缺失！');
        }

        Be::getDb()->startTransaction();
        try {

            if (is_array($primaryKeyValue)) {
                foreach ($primaryKeyValue as $x) {
                    $tuple = Be::newTuple($setting['table']);
                    $tuple->load($x);
                    $this->trigger('BeforeDelete', $tuple);
                    $tuple->delete();
                    $this->trigger('AfterDelete', $tuple);

                    beSystemLog($setting['title'] . '：删除' . $primaryKey . '为' . $x . '的记录！');
                }
            } else {
                $tuple = Be::newTuple($setting['table']);
                $tuple->load($primaryKeyValue);
                $this->trigger('BeforeDelete', $tuple);
                $tuple->delete();
                $this->trigger('AfterDelete', $tuple);

                beSystemLog($setting['title'] . '：删除' . $primaryKey . '为' . $primaryKeyValue . '的记录！');
            }

            Be::getDb()->commit();
        } catch (\Exception $e) {

            Be::getDb()->rollback();
            Response::error($e->getMessage());
        }

        Response::success('删除成功！');
    }


    /*
     * 导出
     *
     */
    public function export()
    {
        $postData = Request::post('data', '', '');
        $postData = json_decode($postData, true);

        $table = Be::newTable($this->setting['table'], $this->setting['db']);

        try {
            if (isset($this->setting['lists']['filter']) && count($this->setting['lists']['filter']) > 0) {
                foreach ($this->setting['lists']['filter'] as $filter) {
                    $table->where($filter);
                }
            }

            $formData = $postData['formData'];
            if (isset($this->setting['lists']['tab'])) {
                if (isset($item['buildSql']) && $item['buildSql'] instanceof \Closure) {
                    $buildSql = $item['buildSql'];
                    $sql = $buildSql($this->setting['db'], $formData);
                    if ($sql) {
                        $table->where($sql);
                    }
                } else {
                    $driver = new \Be\Plugin\Curd\Tab($this->setting['lists']['tab']);
                    $driver->submit($formData);
                    $sql = $driver->buildSql($this->setting['db']);
                    if ($sql) {
                        $table->where($sql);
                    }
                }
            }

            if (isset($this->setting['lists']['search']['items']) && count($this->setting['lists']['search']['items']) > 0) {
                foreach ($this->setting['lists']['search']['items'] as $item) {

                    if (isset($item['buildSql']) && $item['buildSql'] instanceof \Closure) {
                        $buildSql = $item['buildSql'];
                        $sql = $buildSql($this->setting['db'], $formData);
                        if ($sql) {
                            $table->where($sql);
                        }
                    } else {
                        $driver = null;
                        if (isset($item['driver'])) {
                            $driverName = $item['driver'];
                            $driver = new $driverName($item);
                        } else {
                            $driver = new \Be\Plugin\Curd\SearchItem\SearchItemInput($item);
                        }
                        $driver->submit($formData);
                        $sql = $driver->buildSql($this->setting['db']);
                        if ($sql) {
                            $table->where($sql);
                        }
                    }
                }
            }

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
            if (isset($this->setting['export']['field']['items'])) {
                $fields = $this->setting['export']['field']['items'];
            } else {
                $fields = $this->setting['lists']['field']['items'];
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
                    $driver = new \Be\Plugin\Curd\FieldItem\FieldItemText($item);
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

            beSystemLog($content, $formData);

        } catch (\Exception $e) {
            Response::error($e->getMessage());
        }

    }


}

