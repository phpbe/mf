<?php

namespace Be\Plugin\Curd;

use Be\System\Be;
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

    protected $setting = null;

    public function execute($setting = [])
    {
        if (!isset($setting['db'])) {
            $setting['db'] = 'master';
        }

        $this->setting = $setting;

        $task = Request::request('task', 'lists');
        if (isset($this->setting[$task]) && method_exists($this, $task)) {
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
                    if (isset($item['buildSql']) && is_callable($item['buildSql'])){
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

                        if (isset($item['buildSql']) && is_callable($item['buildSql'])){
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
                            if (is_callable($value)) {
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
                            if (is_callable($keyValues)) {
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
        $setting = $this->setting['detail'];

        $tuple = Be::newTuple($setting['table']);

        $primaryKey = $tuple->getPrimaryKey();
        $primaryKeyValue = Request::get($primaryKey, null);

        if (!$primaryKeyValue) {
            Response::error('参数（' . $primaryKey . '）缺失！');
        }

        $tuple->load($primaryKeyValue);
        if (!$tuple->$primaryKey) {
            Response::error('主键编号（' . $primaryKey . '）为 ' . $primaryKeyValue . ' 的记录不存在！');
        }

        $fields = $tuple->getFields();

        Response::setTitle($setting['title']);
        Response::set('row', $tuple);

        $theme = isset($this->setting['detail']['theme']) ?? 'Nude';
        Response::display('Plugin.Curd.detail', $theme);
    }

    /**
     * 创建
     *
     */
    public function create()
    {
        $setting = $this->setting['create'];

        $tuple = Be::newTuple($setting['table']);

        if (Request::isPost()) {

            if (isset($this->setting['create']['BeforeCreate'])) {
                $this->on('BeforeCreate', $this->setting['create']['BeforeCreate']);
            }

            if (isset($this->setting['create']['AfterCreate'])) {
                $this->on('AfterCreate', $this->setting['create']['AfterCreate']);
            }

            $db = Be::getDb($this->setting['db']);
            $db->startTransaction();
            try {
                $tuple->bind(Request::post());
                $primaryKey = $tuple->getPrimaryKey();
                unset($tuple->$primaryKey);
                $this->trigger('BeforeCreate', $tuple);
                $tuple->save();
                $this->trigger('AfterCreate', $tuple);

                beSystemLog($setting['title'] . '：创建' . $primaryKey . '为' . $tuple->$primaryKey . '的记录！');

                $db->commit();
            } catch (\Exception $e) {

                $db->rollback();
                Response::error($e->getMessage());
            }

            Response::success('创建成功！');

        } else {
            Response::setTitle($setting['title']);
            Response::set('row', $tuple);

            $theme = isset($this->setting['create']['theme']) ?? 'Nude';
            Response::display('Plugin.Curd.create', $theme);
        }
    }

    /**
     * 编辑
     *
     */
    public function edit()
    {
        $setting = $this->setting['edit'];

        $tuple = Be::newTuple($setting['table']);

        $primaryKey = $tuple->getPrimaryKey();
        $primaryKeyValue = Request::get($primaryKey, null);

        if (!$primaryKeyValue) {
            Response::error('参数（' . $primaryKey . '）缺失！');
        }

        $tuple->load($primaryKeyValue);
        if (!$tuple->$primaryKey) {
            Response::error('主键编号（' . $primaryKey . '）为 ' . $primaryKeyValue . ' 的记录不存在！');
        }

        if (Request::isPost()) {

            if (isset($this->setting['edit']['BeforeEdit'])) {
                $this->on('BeforeEdit', $this->setting['edit']['BeforeEdit']);
            }

            if (isset($this->setting['edit']['AfterEdit'])) {
                $this->on('AfterEdit', $this->setting['edit']['AfterEdit']);
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

            Response::setTitle($setting['title']);
            Response::set('tuple', $tuple);

            $theme = isset($this->setting['edit']['theme']) ?? 'Nude';
            Response::display('Plugin.Curd.edit', $theme);
        }
    }

    /**
     * 编辑某个字段的值
     */
    public function fieldEdit()
    {
        if (isset($this->setting['fieldEdit']['BeforeFieldEdit'])) {
            $this->on('BeforeFieldEdit', $this->setting['fieldEdit']['BeforeFieldEdit']);
        }

        if (isset($this->setting['fieldEdit']['AfterFieldEdit'])) {
            $this->on('AfterFieldEdit', $this->setting['fieldEdit']['AfterFieldEdit']);
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
                if (isset($item['buildSql']) && is_callable($item['buildSql'])){
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

                    if (isset($item['buildSql']) && is_callable($item['buildSql'])){
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



            $formattedRows = [];
            foreach ($rows as $row) {
                $formattedRow = [];

                foreach ($this->setting['lists']['field']['items'] as $item) {
                    $itemName = $item['name'];
                    $itemValue = '';
                    if (isset($item['value'])) {
                        $value = $item['value'];
                        if (is_callable($value)) {
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
                        if (is_callable($keyValues)) {
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





        $setting = $this->setting['export'];

        $table = Be::newTable($setting['table']);


        $lists = $table->getYieldArrays();

        $type = isset($setting['type']) ? $setting['type'] : 'csv';


        $headers = [];
        $rows = [];
        Be::getPlugin('Exporter')->execute([
            'type' => 'csv',
            'headers' => $headers,
            'rows' => $rows,
        ]);


        $exporter = Exporter::newDriver($type);
        $exporter->config($setting);
        $exporter->start();

        $headers = array();
        $fields = $table->getFields();
        foreach ($fields as $field) {
            if ($field['disable']) continue;

            $headers[] = $field['name'];
        }
        $exporter->setHeader($headers);

        $fields = $table->getFields();
        foreach ($lists as &$x) {
            self::formatField($x, $fields);
            $exporter->addRow($x);
        }
        $exporter->end();

        beSystemLog($setting['title']);
    }


}

