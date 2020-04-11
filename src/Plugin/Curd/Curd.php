<?php

namespace Be\Plugin\Curd;

use Be\Plugin\Lists\Field\FieldItemText;
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


    /**
     * 列表展示
     *
     * @param array $setting 配置项
     */
    public function lists($setting = [])
    {

        $setting['total'] = 100;
        $setting['pageSize'] = 20;
        $setting['page'] = 1;

        $pluginLists = Be::getPlugin('Lists');
        $pluginLists->execute($setting);

        $runtime = Be::getRuntime();
        $appName = $runtime->getAppName();
        $controllerName = $runtime->getControllerName();
        $actionName = $runtime->getActionName();

        $table = Be::newTable($setting['table']);

        $primaryKey = $table->getPrimaryKey();

        $searchDrivers = [];
        if (isset($setting['search']['items'])) {
            foreach ($setting['search']['items'] as $item) {
                $driver = $item['driver'];
                $searchDriver = new $driver($item);
                $searchDrivers[] = $searchDriver;
            }
        }

        $toolbarDrivers = [];
        if (isset($setting['toolbar']['items'])) {
            foreach ($setting['toolbar']['items'] as $item) {
                $driver = $item['driver'];
                $toolbarDriver = new $driver($item);
                $toolbarDrivers[] = $toolbarDriver;
            }
        }

        $page = Request::post('page', 1, 'int');

        $pageSize = Request::post('pageSize', 0, 'int');
        $cookiePageSizeKey = $appName . '.' . $controllerName . '.' . $actionName . '.pageSize';
        if (!$pageSize) {
            $cookiePageSize = Cookie::get($cookiePageSizeKey, 0, 'int');
            if ($cookiePageSize > 0) {
                $pageSize = $cookiePageSize;
            } else {
                $pageSize = Be::getConfig('System.System')->pageSize;;
            }
        }

        Cookie::set($cookiePageSizeKey, $pageSize, 86400 * 30);

        if ($pageSize <= 0) $pageSize = Be::getConfig('System.System')->pageSize;;
        if ($pageSize > 1000) $pageSize = 1000;

        $total = $table->count();

        $pages = ceil($total / $pageSize);
        if ($pages == 0) $pages = 1;

        $table->offset(($page - 1) * $pageSize)->limit($pageSize);

        $orderBy = Request::post('orderBy', $primaryKey);
        $orderByDir = Request::post('orderByDir', 'DESC');
        $table->orderBy($orderBy, $orderByDir);

        $rows = $table->getObjects();

        $fields = null;
        if (isset($config['field']['items'])) {
            $fields = $config['field']['items'];
        } else {
            $tableConfig = Be::getTableProperty($config['table']);
            $fields = $tableConfig->getFields();
        }

        $fieldDrivers = [];
        foreach ($rows as $row) {

            $tmpFieldDrivers = [];
            foreach ($fields as $item) {

                if (!isset($item['value']) && isset($item['name'])) {
                    $name = $item['name'];
                    if (isset($row->$name)) {
                        $item['value'] = $row->$name;
                    }
                }

                $driver = null;
                if (!isset($item['driver'])) {
                    $driver = FieldItemText::class;
                } else {
                    $driver = $item['driver'];
                }
                $fieldDriver = new $driver($item);
                $tmpFieldDrivers[] = $fieldDriver;
            }

            $fieldDrivers[] = $tmpFieldDrivers;
        }



        Response::setTitle($config['title']);
        Response::set('config', $config);
        Response::set('table', $table);

        Response::set('searchDrivers', $searchDrivers);
        Response::set('toolbarDrivers', $toolbarDrivers);
        Response::set('fieldDrivers', $fieldDrivers);

        Response::set('page', $page);
        Response::set('pageSize', $pageSize);
        Response::set('pages', $pages);
        Response::set('total', $total);
        Response::set('orderBy', $orderBy);
        Response::set('orderByDir', $orderByDir);
        Response::display('Plugin.Curd.lists');
        Response::createHistory();
    }

    /**
     * 明细
     *
     * @param array $setting 配置项
     */
    public function detail($setting = [])
    {
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
     * @param array $setting 配置项
     */
    public function create($setting = [])
    {
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

                SystemLog($setting['title'] . '：创建' . $primaryKey . '为' . $tuple->$primaryKey . '的记录！');

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
     * @param array $setting 配置项
     */
    public function edit($setting = [])
    {
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

                SystemLog($setting['title'] . '：编辑' . $primaryKey . '为' . $primaryKeyValue . '的记录！');

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
     * @param array $setting 配置项
     */
    public function toggle($setting = [])
    {
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

                    $field = 'block';
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

                    SystemLog($setting['title'] . '（记录编号 ' . $primaryKey . '为' . $x . '）');
                }
            } else {

                $field = 'block';
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

                SystemLog($setting['title'] . '（记录编号 ' . $primaryKey . '为' . $x . '）');
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
     * @param array $setting 配置项
     */
    public function delete($setting = [])
    {
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

                    SystemLog($setting['title'] . '：删除' . $primaryKey . '为' . $x . '的记录！');
                }
            } else {
                $tuple = Be::newTuple($setting['table']);
                $tuple->load($primaryKeyValue);
                $this->trigger('BeforeDelete', $tuple);
                $tuple->delete();
                $this->trigger('AfterDelete', $tuple);

                SystemLog($setting['title'] . '：删除' . $primaryKey . '为' . $primaryKeyValue . '的记录！');
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
     * @param array $setting 配置项
     */
    public function export($setting = [])
    {
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

        systemLog($setting['title']);
    }


}

