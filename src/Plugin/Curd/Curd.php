<?php

namespace Be\Plugin\Curd;

use Be\System\Be;
use Be\System\Db\Tuple;
use Be\System\Event;
use Be\System\Request;
use Be\System\Response;
use Be\System\Cookie;

/**
 * 增删改查
 *
 * Class Curd
 * @package Be\Plugin
 */
class Curd
{


    public function execute( $config ) {

    }

    // 注册事件
    public function on($event, $callback) {
        Event::on('Plugin.Curd.'.$event, $callback);
    }

    /**
     * 列表展示
     */
    public function lists($config = [])
    {
        $listor = Be::getPlugin('Listor');


        $app = Be::getRuntime()->getAppName();
        $controller = Be::getRuntime()->getControllerName();

        $table = Be::newTable($config['table']);

        $primaryKey = $table->getPrimaryKey();

        $searchDrivers = [];
        if (isset($config['search']['items'])) {
            foreach($config['search']['items'] as $key => $search) {
                $driver = $search['driver'];
                $searchDriver = new $driver($key, $search);
                $searchDriver->buildWhere(Request::post());

                $searchDrivers[] = $searchDriver;
            }
        }

        if (isset($config['toolbar']['items'])) {
            foreach($config['toolbar']['items'] as &$toolbar) {
                if (isset($toolbar['action']) && $toolbar['action']) {
                    $toolbar['url'] = url($app . '.' . $controller . '.' . $toolbar['action']);
                }
            }
        }

        $page = Request::post('page', 1, 'int');
        $pageSize = Request::post('pageSize', 0, 'int');
        $defaultPageSize = Be::getConfig('System.Admin')->pageSize;

        $cookiePageSizeKey = '_' . $app . '_' . $controller . '_pageSize';
        if (!$pageSize) {
            $cookiePageSize = Cookie::get($cookiePageSizeKey, 0, 'int');
            if ($cookiePageSize > 0) {
                $pageSize = $cookiePageSize;
            } else {
                $pageSize = $defaultPageSize;
            }
        }

        Cookie::set($cookiePageSizeKey, $pageSize, 86400 * 30);


        if ($pageSize <= 0) $pageSize = $defaultPageSize;
        if ($pageSize >1000) $pageSize = 1000;

        $total = $table->count();

        $pages = ceil($total / $pageSize);
        if ($pages == 0) $pages = 1;

        $table->offset(($page-1)*$pageSize)->limit($pageSize);

        $orderBy = Request::post('orderBy', $primaryKey);
        $orderByDir = Request::post('orderByDir', 'DESC');
        $table->orderBy($orderBy, $orderByDir);

        $data = $table->getObjects();

        $fields = null;
        if (isset($config['field']['items'])) {
            $fields = $config['field']['items'];
        } else {
            $tableConfig = Be::getTableProperty($config['table']);
            $fields = $tableConfig->getFields();
        }

        foreach ($data as &$x) {
            self::formatField($x, $fields);
        }

        Response::setTitle($config['name'] . '：列表');
        Response::set('config', $config);
        Response::set('table', $table);
        Response::set('searchDrivers', $searchDrivers);
        Response::set('data', $data);
        Response::set('page', $page);
        Response::set('pageSize', $pageSize);
        Response::set('pages', $pages);
        Response::set('total', $total);
        Response::set('orderBy', $orderBy);
        Response::set('orderByDir', $orderByDir);
        Response::display('Plugin', 'Curd.lists');
        Response::createHistory();
    }

    /**
     * 明细
     */
    public function detail($config = [])
    {
        $tuple = Be::newTuple($config['table']);

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
        self::formatField($tuple, $fields);

        Response::setTitle($config['name'] . '：明细');
        Response::set('row', $tuple);
        Response::display('Plugin.Curd.detail');
    }

    /**
     * 创建
     */
    public function create($config = [])
    {
        $tuple = Be::newTuple($config['table']);

        if (Request::isPost()) {

            Be::getDb()->startTransaction();
            try {
                $tuple->bind(Request::post());
                $primaryKey = $tuple->getPrimaryKey();
                unset($tuple->$primaryKey);
                Event::trigger('Plugin.Curd.BeforeCreate', $tuple);
                $tuple->save();
                Event::trigger('Plugin.Curd.AfterCreate', $tuple);

                SystemLog($config['name'] . '：创建' . $primaryKey . '为' . $tuple->$primaryKey . '的记录！');

                Be::getDb()->commit();
            } catch (\Exception $e) {

                Be::getDb()->rollback();
                Response::error($e->getMessage());
            }

            Response::success('创建成功！');

        } else {
            Response::setTitle($config['name'] . '：创建');
            Response::set('row', $tuple);
            Response::display('Plugin', 'Curd.create');
        }
    }

    /**
     * 编辑
     */
    public function edit($config = [])
    {
        $tuple = Be::newTuple($config['table']);

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
                Event::trigger('Plugin.Curd.BeforeEdit', $tuple);
                $tuple->save();
                Event::trigger('Plugin.Curd.AfterEdit', $tuple);

                SystemLog($config['name'] . '：编辑' . $primaryKey . '为' . $primaryKeyValue . '的记录！');

                Be::getDb()->commit();
            } catch (\Exception $e) {

                Be::getDb()->rollback();
                Response::error($e->getMessage());
            }

            Response::success('修改成功！');

        } else {

            Response::setTitle($config['name'] . '：编辑');
            Response::set('tuple', $tuple);
            Response::display('Plugin.Curd.edit');
        }
    }

    public function block($config = [])
    {
        $tuple = Be::newTuple($config['table']);

        $primaryKey = $tuple->getPrimaryKey();
        $primaryKeyValue = Request::get($primaryKey, null);

        if (!$primaryKeyValue) {
            Response::error('参数（' . $primaryKey . '）缺失！');
        }

        Be::getDb()->startTransaction();
        try {

            if (is_array($primaryKeyValue)) {

                foreach ($primaryKeyValue as $x) {

                    $field = 'block';
                    if (isset($config['field'])) {
                        $field = $config['field'];
                    }

                    $value = 1;
                    if (isset($config['value'])) {
                        $value = $config['value'];
                    }

                    $tuple->load($primaryKeyValue);
                    $tuple->$field = $value;
                    Event::trigger('Plugin.Curd.BeforeBlock', $tuple);
                    $tuple->save();
                    Event::trigger('Plugin.Curd.AfterBlock', $tuple);

                    SystemLog($config['name'] . '：禁用' . $primaryKey . '为' . $x . '的记录！');
                }
            } else {

                $field = 'block';
                if (isset($config['field'])) {
                    $field = $config['field'];
                }

                $value = 1;
                if (isset($config['value'])) {
                    $value = $config['value'];
                }

                $tuple->load($primaryKeyValue);
                $tuple->$field = $value;
                Event::trigger('Plugin.Curd.BeforeBlock', $tuple);
                $tuple->save();
                Event::trigger('Plugin.Curd.AfterBlock', $tuple);

                SystemLog($config['name'] . '：禁用' . $primaryKey . '为' . $primaryKeyValue . '的记录！');
            }

            Be::getDb()->commit();
        } catch (\Exception $e) {

            Be::getDb()->rollback();
            Response::error($e->getMessage());
        }

        Response::success('禁用成功！');
    }


    public function unblock($config = [])
    {
        $tuple = Be::newTuple($config['table']);

        $primaryKey = $tuple->getPrimaryKey();
        $primaryKeyValue = Request::get($primaryKey, null);

        if (!$primaryKeyValue) {
            Response::error('参数（' . $primaryKey . '）缺失！');
        }

        Be::getDb()->startTransaction();
        try {

            if (is_array($primaryKeyValue)) {

                foreach ($primaryKeyValue as $x) {

                    $field = 'block';
                    if (isset($config['field'])) {
                        $field = $config['field'];
                    }

                    $value = 0;
                    if (isset($config['value'])) {
                        $value = $config['value'];
                    }

                    $tuple->load($primaryKeyValue);
                    $tuple->$field = $value;
                    Event::trigger('Plugin.Curd.BeforeUnblock', $tuple);
                    $tuple->save();
                    Event::trigger('Plugin.Curd.AfterUnblock', $tuple);

                    SystemLog($config['name'] . '：启用' . $primaryKey . '为' . $x . '的记录！');
                }
            } else {

                $field = 'block';
                if (isset($config['field'])) {
                    $field = $config['field'];
                }

                $value = 0;
                if (isset($config['value'])) {
                    $value = $config['value'];
                }

                $tuple->load($primaryKeyValue);
                $tuple->$field = $value;
                Event::trigger('Plugin.Curd.BeforeUnblock', $tuple);
                $tuple->save();
                Event::trigger('Plugin.Curd.AfterUnblock', $tuple);

                SystemLog($config['name'] . '：启用' . $primaryKey . '为' . $primaryKeyValue . '的记录！');
            }

            Be::getDb()->commit();
        } catch (\Exception $e) {

            Be::getDb()->rollback();
            Response::error($e->getMessage());
        }

        Response::success('启用成功！');
    }


    /**
     * 删除
     */
    public function delete($config = [])
    {
        $tuple = Be::newTuple($config['table']);

        $primaryKey = $tuple->getPrimaryKey();
        $primaryKeyValue = Request::get($primaryKey, null);

        if (!$primaryKeyValue) {
            Response::error('参数（' . $primaryKey . '）缺失！');
        }

        Be::getDb()->startTransaction();
        try {

            if (is_array($primaryKeyValue)) {
                foreach ($primaryKeyValue as $x) {
                    if (isset($config['field'])) {

                        $field = 'block';
                        if (isset($config['field'])) {
                            $field = $config['field'];
                        }

                        $value = 1;
                        if (isset($config['value'])) {
                            $value = $config['value'];
                        }

                        $tuple = Be::newTuple($config['table']);
                        $tuple->load($x);
                        $tuple->$field = $value;
                        Event::trigger('Plugin.Curd.BeforeDelete', $tuple);
                        $tuple->save();
                        Event::trigger('Plugin.Curd.AfterDelete', $tuple);
                    } else {
                        $tuple = Be::newTuple($config['table']);
                        $tuple->load($x);
                        Event::trigger('Plugin.Curd.BeforeDelete', $tuple);
                        $tuple->save();
                        Event::trigger('Plugin.Curd.AfterDelete', $tuple);
                    }

                    SystemLog($config['name'] . '：删除' . $primaryKey . '为' . $x . '的记录！');
                }
            } else {

                if (isset($config['field'])) {

                    $field = 'block';
                    if (isset($config['field'])) {
                        $field = $config['field'];
                    }

                    $value = 1;
                    if (isset($config['value'])) {
                        $value = $config['value'];
                    }

                    $tuple->load($primaryKeyValue);
                    $tuple->$field = $value;
                    Event::trigger('Plugin.Curd.BeforeDelete', $tuple);
                    $tuple->save();
                    Event::trigger('Plugin.Curd.AfterDelete', $tuple);
                } else {
                    $tuple = Be::newTuple($config['table']);
                    $tuple->load($primaryKeyValue);
                    Event::trigger('Plugin.Curd.BeforeDelete', $tuple);
                    $tuple->save();
                    Event::trigger('Plugin.Curd.AfterDelete', $tuple);
                }

                SystemLog($config['name'] . '：删除' . $primaryKey . '为' . $primaryKeyValue . '的记录！');
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
     */
    public function export($config = [])
    {
        $table = Be::newTable($config['table']);

        foreach($config['search'] as $key => $search) {
            $driver = $search['driver'];
            $searchDriver = new $driver($key, $search);
            $searchDriver->buildWhere($table, Request::post());
        }

        $lists = $table->getYieldArrays();

        $type = isset($config['type']) ? $config['type'] : 'csv';

        $exporter = Exporter::newDriver($type);
        $exporter->config($config);
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

        systemLog($config['name'] . '：导出记录！');
    }


    protected static function formatField(&$row, $fields) {

        foreach ($fields as $field) {

            $name = $field['name'];

            if (isset($field['value'])) {
                if (is_callable($field['value'])) {

                    $fn = $field['value'];
                    $row->$name = $fn($row);

                } else {
                    $row->$name = $field['value'];
                }

            } else {

                if (isset($field['keyValues'])) {
                    if (isset($field['keyValues'][$row->$name])) {
                        $row->$name = $field['keyValues'][$row->$name];
                    } else {
                        $row->$name = '-';
                    }
                }

            }
        }
    }



}

