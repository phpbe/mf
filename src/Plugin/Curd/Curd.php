<?php

namespace Be\Plugin\Curd;

use Be\Plugin\Lists\ListItem\ListItemText;
use Be\System\Be;
use Be\System\Db\Tuple;
use Be\System\Plugin;
use Be\System\Request;
use Be\System\Response;
use Be\System\Cookie;

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
        $table = Be::newTable($this->setting['table']);

        if (Request::isAjax()) {

            try {
                $postData = Request::json();
                $searchForm = $postData['searchForm'];
                $page = $postData['page'];
                $pageSize = $postData['pageSize'];

                $total = $table->count();

                $pages = ceil($total / $pageSize);
                if ($pages == 0) $pages = 1;

                $table->offset(($page - 1) * $pageSize)->limit($pageSize);

                if (isset($this->setting['lists']['tab'])) {
                    $driver = new \Be\Plugin\Lists\Tab();
                    $driver->submit($searchForm);
                    $where = $driver->buildSql();
                    if ($where) {
                        $table->where($where);
                    }
                }

                if (isset($this->setting['lists']['search']['items']) && count($this->setting['lists']['search']['items']) > 0) {
                    foreach ($this->setting['lists']['search']['items'] as $item) {
                        $driver = isset($item['driver']) ? $item['driver'] : '\\Be\\Plugin\\Lists\\SearchItem\\SearchItemInput';
                        $driver = new $driver($item);
                        $driver->submit($searchForm);
                        $where = $driver->buildSql();
                        if ($where) {
                            foreach ($where as $w) {
                                $table->where($w);
                            }
                        }
                    }
                }

                $orderBy = Request::post('orderBy');
                if ($orderBy) {
                    $orderByDir = Request::post('orderByDir', 'DESC');
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
                    } else {
                        $primaryKey = $table->getPrimaryKey();
                        if (is_array($primaryKey)) {
                            $orderByStrings = [];
                            foreach ($primaryKey as $pKey) {
                                $orderByStrings[] = $pKey . ' DESC';
                            }
                            $table->orderBy(implode(',', $orderByStrings));
                        } else {
                            $table->orderBy($primaryKey, 'DESC');
                        }
                    }
                }

                $tuples = $table->getObjects();

                Response::set('success', true);
                Response::set('data', [
                    'total' => $total,
                    'tuples' => $tuples,
                ]);
                Response::ajax();
            } catch (\Exception $e) {
                Response::set('success', false);
                Response::set('message', $e->getMessage());
                Response::ajax();
            }

        } else {

            $pageSize = null;
            if (isset($this->setting['lists']['defaultPageSize']) &&
                is_numeric($this->setting['lists']['defaultPageSize']) &&
                $this->setting['lists']['defaultPageSize'] > 0
            ) {
                $pageSize = $this->setting['lists']['defaultPageSize'];
            } else {
                $pageSize = Be::getConfig('System.System')->pageSize;;
            }

            Response::setTitle($this->setting['lists']['title']);

            Response::set('url', Request::url());
            Response::set('setting', $this->setting);
            Response::set('table', $table);
            Response::set('pageSize', $pageSize);
            Response::display('Plugin.Curd.lists');
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
        Response::display('Plugin.Curd.detail');
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

            Be::getDb()->startTransaction();
            try {
                $tuple->bind(Request::post());
                $primaryKey = $tuple->getPrimaryKey();
                unset($tuple->$primaryKey);
                $this->trigger('BeforeCreate', $tuple);
                $tuple->save();
                $this->trigger('AfterCreate', $tuple);

                beSystemLog($setting['title'] . '：创建' . $primaryKey . '为' . $tuple->$primaryKey . '的记录！');

                Be::getDb()->commit();
            } catch (\Exception $e) {

                Be::getDb()->rollback();
                Response::error($e->getMessage());
            }

            Response::success('创建成功！');

        } else {
            Response::setTitle($setting['title']);
            Response::set('row', $tuple);
            Response::display('Plugin.Curd.create');
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

            Be::getDb()->startTransaction();
            try {

                $tuple->bind(Request::post());
                $this->trigger('BeforeEdit', $tuple);
                $tuple->save();
                $this->trigger('AfterEdit', $tuple);

                beSystemLog($setting['title'] . '：编辑' . $primaryKey . '为' . $primaryKeyValue . '的记录！');

                Be::getDb()->commit();
            } catch (\Exception $e) {

                Be::getDb()->rollback();
                Response::error($e->getMessage());
            }

            Response::success('修改成功！');

        } else {

            Response::setTitle($setting['title']);
            Response::set('tuple', $tuple);
            Response::display('Plugin.Curd.edit');
        }
    }

    /**
     * 切换某个字段的值，示例功能：启用/禁用
     *
     */
    public function toggle()
    {
        $setting = $this->setting['toggle'];

        $field = Request::request('field', 'block');
        $value = Request::request('value', 1);

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

                    if (isset($setting['field'])) {
                        $field = $setting['field'];
                    }

                    if (isset($setting['value'])) {
                        $value = $setting['value'];
                    }

                    $tuple->load($x);
                    $tuple->$field = $value;
                    $this->trigger('BeforeToggle', $tuple);
                    $tuple->save();
                    $this->trigger('AfterToggle', $tuple);

                    beSystemLog($setting['title'] . '（#' . $primaryKey . '：' . $x . '）');
                }
            } else {

                if (isset($setting['field'])) {
                    $field = $setting['field'];
                }

                if (isset($setting['value'])) {
                    $value = $setting['value'];
                }

                $tuple->load($primaryKeyValue);
                $tuple->$field = $value;
                $this->trigger('BeforeToggle', $tuple);
                $tuple->save();
                $this->trigger('AfterToggle', $tuple);

                beSystemLog($setting['title'] . '（#' . $primaryKey . '：' . $primaryKeyValue . '）');
            }

            Be::getDb()->commit();
        } catch (\Exception $e) {

            Be::getDb()->rollback();
            Response::error($e->getMessage());
        }

        Response::success($setting['title'] . '，执行成功！');
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
        $setting = $this->setting['export'];

        $table = Be::newTable($setting['table']);

        foreach ($setting['search'] as $key => $search) {
            $driver = $search['driver'];
            $searchDriver = new $driver($key, $search);
            $searchDriver->buildWhere($table, Request::post());
        }

        $lists = $table->getYieldArrays();

        $type = isset($setting['type']) ? $setting['type'] : 'csv';

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

