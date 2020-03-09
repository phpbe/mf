<?php

namespace Be\App\System\AdminTrait;

use Be\System\Be;
use Be\System\Db\Tuple;
use Be\System\Request;
use Be\System\Response;
use Be\System\Cookie;

/**
 * 增删改查
 *
 * Class Curd
 * @package App\System\AdminTrait
 */
trait Curd
{

    public $config = [];

    /*
     * 在子类中给 $config 赋值，示例值如下：
    public $config = [

        'name' => '公告',

        'table' => 'System.Announcement',

        // 启用的功能
        'action' => [

            // 列表功能
            'lists' => [

                // 搜索项
                'search' => [
                    'title' => [
                        'name' => '标题',
                        'driver' => \Be\System\App\SearchItem\SearchItemString::class,
                        'uiType' => 'text',
                        'operation' => '%like%',    // =|!=|>|<|>=|<=|%like%|like%|%like
                    ],
                 ],

                'toolbar' => [
                    [
                        'name' => '新建',
                        'action' => 'create',
                        'url' => '',
                        'icon' => '',
                        'class' => '',
                        'style' => '',
                    ],
                    [
                        'name' => '导出',
                        'action' => 'export',
                        'url' => '',
                        'icon' => '',
                        'class' => '',
                        'style' => '',
                    ],
                ],

                'operation' => [
                    [
                        'name' => '查看',
                        'action' => 'detail',
                        'url' => '',
                        'icon' => '',
                        'class' => '',
                        'style' => '',
                    ],
                    [
                        'name' => '编辑',
                        'action' => 'edit',
                        'url' => '',
                        'icon' => '',
                        'class' => '',
                        'style' => '',
                    ],
                ],

                'field' => [], // 控制列表展示的字段，不设置时取 tableConfig 中的配置

                'extField' => [], // 额外添加的字段，用于展示 table 中不存在的附加内容。

                'checkbox' => true,  // 是否在每一行显示复选框，未配置时，如果 toolbar 中有 unblock|block|delete 项，checkbox 自动赋值为 true，
            ],

            // 查看明细功能
            'detail' => [],

            // 创建功能
            'create' => [],

            // 编辑功能
            'edit' => [],

            // 禁用功能
            'block' => [
                'field' => 'block',
                'value' => 1
            ],

            // 启用功能
            'unblock' => [
                'field' => 'block',
                'value' => 0
            ],

            // 删除功能
            'delete' => [
                'field' => 'is_delete', // 设置 field 时为软删除，仅标记删除
                'value' => 1
            ],

            // 导出功能
            'export' => [],
        ]
    ];
    */

    /**
     * 列表展示
     */
    public function lists()
    {
        if (!isset($this->config['action']['lists'])) {
            Response::end($this->config['name'] . '：列表功能未开启');
        }

        $app = Be::getRuntime()->getAppName();
        $controller = Be::getRuntime()->getControllerName();

        $table = Be::newTable($this->config['table']);

        $primaryKey = $table->getPrimaryKey();

        $searchDrivers = [];
        if (isset($this->config['action']['lists']['search'])) {
            foreach($this->config['action']['lists']['search'] as $key => $search) {
                $driver = $search['driver'];
                $searchDriver = new $driver($key, $search);
                $searchDriver->buildWhere($table, Request::post());

                $searchDrivers[] = $searchDriver;
            }
        }

        if (isset($this->config['action']['lists']['toolbar'])) {
            foreach($this->config['action']['lists']['toolbar'] as &$toolbar) {
                if (isset($toolbar['action']) && $toolbar['action']) {
                    $toolbar['url'] = adminUrl($app . '.' . $controller . '.' . $toolbar['action']);
                }
            }
        }

        $offset = Request::post('offset', 0, 'int');
        $limit = Request::post('limit', 0, 'int');
        if ($limit < 0) $limit = 0;

        $cookieLimitKey = '_' . $app . '_' . $controller . '_limit';

        if (!$limit) {
            $cookieLimit = Cookie::get($cookieLimitKey, 0, 'int');
            if ($cookieLimit > 0) {
                $limit = $cookieLimit;
            } else {
                $limit = Be::getConfig('System', 'Admin')->limit;
            }
        } else {
            Cookie::set($cookieLimitKey, $limit, 86400 * 30);
        }

        $pagination = Be::getUi('Pagination');
        $pagination->setLimit($limit);
        $pagination->setTotal($table->count());
        $pagination->setPage(Request::post('page', 1, 'int'));

        $table->offset($offset)->limit($limit);

        $orderBy = Request::post('orderBy', $primaryKey);
        $orderByDir = Request::post('orderByDir', 'DESC');
        $table->orderBy($orderBy, $orderByDir);

        $data = $table->getObjects();

        $fields = null;
        if (isset($this->config['action']['lists']['field'])) {
            $fields = $this->config['action']['lists']['field'];
        } else {
            $tableConfig = Be::newTableConfig($this->config['table']);
            $fields = $tableConfig->getFields();
        }

        foreach ($data as &$x) {
            $this->formatField($x, $fields);
        }

        if (isset($this->config['action']['lists']['extField'])) {
            $extFields = $this->config['action']['lists']['extField'];

            foreach ($data as &$x) {
                $this->formatExtField($x, $extFields);
            }
        }

        Response::setTitle($this->config['name'] . '：列表');
        Response::set('config', $this->config);
        Response::set('table', $table);
        Response::set('searchDrivers', $searchDrivers);
        Response::set('data', $data);
        Response::set('pagination', $pagination);
        Response::set('orderBy', $orderBy);
        Response::set('orderByDir', $orderByDir);
        Response::display('System.AdminTrait.Curd.lists');

        $libHistory = Be::getLib('History');
        $libHistory->save();
    }

    /**
     * 明细
     */
    public function detail()
    {
        if (!isset($this->config['action']['detail'])) {
            Response::end($this->config['name'] . '：明细功能未开启');
        }

        $tuple = Be::newTuple($this->config['table']);

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
        $this->formatField($tuple, $fields);

        Response::setTitle($this->config['name'] . '：明细');
        Response::set('row', $tuple);
        Response::display('System.AdminTrait.Curd.detail');
    }

    /**
     * 创建
     */
    public function create()
    {
        if (!isset($this->config['action']['create'])) {
            Response::end($this->config['name'] . '：创建功能未开启');
        }

        $tuple = Be::newTuple($this->config['table']);

        if (Request::isPost()) {

            Be::getDb()->startTransaction();
            try {
                $tuple->bind(Request::post());
                $primaryKey = $tuple->getPrimaryKey();
                unset($tuple->$primaryKey);
                $this->beforeCreate($tuple);
                $tuple->save();
                $this->afterCreate($tuple);

                Be::getService('System', 'AdminLog')->addLog($this->config['name'] . '：创建' . $primaryKey . '为' . $tuple->$primaryKey . '的记录！');

                Be::getDb()->commit();
            } catch (\Exception $e) {

                Be::getDb()->rollback();
                Response::error($e->getMessage());
            }

            Response::success('创建成功！');

        } else {
            Response::setTitle($this->config['name'] . '：创建');
            Response::set('row', $tuple);
            Response::display('System.AdminTrait.Curd.create');
        }
    }

    /**
     * 创建保存前调用，可抛出异常，事务回滚
     *
     * @param Tuple $tuple 创建的行对象
     * @throws \Exception
     */
    protected function beforeCreate(Tuple $tuple) {}

    /**
     * 创建保存后调用，可抛出异常，事务回滚
     *
     * @param Tuple $tuple 创建的行对象
     * @throws \Exception
     */
    protected function afterCreate(Tuple $tuple) {}


    /**
     * 编辑
     */
    public function edit()
    {
        if (!isset($this->config['action']['edit'])) {
            Response::end($this->config['name'] . '：编辑功能未开启');
        }

        $tuple = Be::newTuple($this->config['table']);

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
                $this->beforeEdit($tuple);
                $tuple->save();
                $this->afterEdit($tuple);

                Be::getService('System', 'AdminLog')->addLog($this->config['name'] . '：编辑' . $primaryKey . '为' . $primaryKeyValue . '的记录！');

                Be::getDb()->commit();
            } catch (\Exception $e) {

                Be::getDb()->rollback();
                Response::error($e->getMessage());
            }

            Response::success('修改成功！');

        } else {

            Response::setTitle($this->config['name'] . '：编辑');
            Response::set('tuple', $tuple);
            Response::display('System.AdminTrait.Curd.edit');
        }
    }

    /**
     * 编辑保存前调用，可抛出异常，事务回滚
     *
     * @param Tuple $tuple 编辑的行对象
     * @throws \Exception
     */
    protected function beforeEdit(Tuple $tuple) {}

    /**
     * 编辑保存后调用，可抛出异常，事务回滚
     *
     * @param Tuple $tuple 编辑的行对象
     * @throws \Exception
     */
    protected function afterEdit(Tuple $tuple) {}


    public function block()
    {
        if (!isset($this->config['action']['block'])) {
            Response::end($this->config['name'] . '：禁用功能未开启');
        }

        $tuple = Be::newTuple($this->config['table']);

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
                    if (isset($this->config['action']['block']['field'])) {
                        $field = $this->config['action']['block']['field'];
                    }

                    $value = 1;
                    if (isset($this->config['action']['block']['value'])) {
                        $value = $this->config['action']['block']['value'];
                    }

                    $tuple->load($primaryKeyValue);
                    $tuple->$field = $value;
                    $this->beforeBlock($tuple);
                    $tuple->save();
                    $this->afterBlock($tuple);

                    Be::getService('System', 'AdminLog')->addLog($this->config['name'] . '：禁用' . $primaryKey . '为' . $x . '的记录！');
                }
            } else {

                $field = 'block';
                if (isset($this->config['action']['block']['field'])) {
                    $field = $this->config['action']['block']['field'];
                }

                $value = 1;
                if (isset($this->config['action']['block']['value'])) {
                    $value = $this->config['action']['block']['value'];
                }

                $tuple->load($primaryKeyValue);
                $tuple->$field = $value;
                $this->beforeBlock($tuple);
                $tuple->save();
                $this->afterBlock($tuple);

                Be::getService('System', 'AdminLog')->addLog($this->config['name'] . '：禁用' . $primaryKey . '为' . $primaryKeyValue . '的记录！');
            }

            Be::getDb()->commit();
        } catch (\Exception $e) {

            Be::getDb()->rollback();
            Response::error($e->getMessage());
        }

        Response::success('禁用成功！');
    }

    /**
     * 禁用前调用，可抛出异常，事务回滚
     *
     * @param Tuple $tuple 行记录
     * @throws \Exception
     */
    protected function beforeBlock(Tuple $tuple) {}

    /**
     * 禁用后调用，可抛出异常，事务回滚
     *
     * @param Tuple $tuple 行记录
     * @throws \Exception
     */
    protected function afterBlock(Tuple $tuple) {}


    public function unblock()
    {
        if (!isset($this->config['action']['unblock'])) {
            Response::end($this->config['name'] . '：启用功能未开启');
        }

        $tuple = Be::newTuple($this->config['table']);

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
                    if (isset($this->config['action']['unblock']['field'])) {
                        $field = $this->config['action']['unblock']['field'];
                    }

                    $value = 0;
                    if (isset($this->config['action']['unblock']['value'])) {
                        $value = $this->config['action']['unblock']['value'];
                    }

                    $tuple->load($primaryKeyValue);
                    $tuple->$field = $value;
                    $this->beforeUnblock($tuple);
                    $tuple->save();
                    $this->afterUnblock($tuple);

                    Be::getService('System', 'AdminLog')->addLog($this->config['name'] . '：启用' . $primaryKey . '为' . $x . '的记录！');
                }
            } else {

                $field = 'block';
                if (isset($this->config['action']['unblock']['field'])) {
                    $field = $this->config['action']['unblock']['field'];
                }

                $value = 0;
                if (isset($this->config['action']['unblock']['value'])) {
                    $value = $this->config['action']['unblock']['value'];
                }

                $tuple->load($primaryKeyValue);
                $tuple->$field = $value;
                $this->beforeUnblock($tuple);
                $tuple->save();
                $this->afterUnblock($tuple);

                Be::getService('System', 'AdminLog')->addLog($this->config['name'] . '：启用' . $primaryKey . '为' . $primaryKeyValue . '的记录！');
            }

            Be::getDb()->commit();
        } catch (\Exception $e) {

            Be::getDb()->rollback();
            Response::error($e->getMessage());
        }

        Response::success('启用成功！');
    }

    /**
     * 删除前调用，可抛出异常，事务回滚
     *
     * @param Tuple $tuple 行记录
     * @throws \Exception
     */
    protected function beforeUnblock(Tuple $tuple) {}

    /**
     * 删除后调用，可抛出异常，事务回滚
     *
     * @param Tuple $tuple 行记录
     * @throws \Exception
     */
    protected function afterUnblock(Tuple $tuple) {}


    /**
     * 删除
     */
    public function delete()
    {
        if (!isset($this->config['action']['delete'])) {
            Response::end($this->config['name'] . '：删除功能未开启');
        }

        $tuple = Be::newTuple($this->config['table']);

        $primaryKey = $tuple->getPrimaryKey();
        $primaryKeyValue = Request::get($primaryKey, null);

        if (!$primaryKeyValue) {
            Response::error('参数（' . $primaryKey . '）缺失！');
        }

        Be::getDb()->startTransaction();
        try {

            if (is_array($primaryKeyValue)) {
                foreach ($primaryKeyValue as $x) {
                    if (isset($this->config['action']['delete']['field'])) {

                        $field = 'block';
                        if (isset($this->config['action']['delete']['field'])) {
                            $field = $this->config['action']['delete']['field'];
                        }

                        $value = 1;
                        if (isset($this->config['action']['delete']['value'])) {
                            $value = $this->config['action']['delete']['value'];
                        }

                        $tuple = Be::newTuple($this->config['table']);
                        $tuple->load($x);
                        $tuple->$field = $value;
                        $this->beforeDelete($tuple);
                        $tuple->save();
                        $this->afterDelete($tuple);
                    } else {
                        $tuple = Be::newTuple($this->config['table']);
                        $tuple->load($x);
                        $this->beforeDelete($tuple);
                        $tuple->delete();
                        $this->afterDelete($tuple);
                    }

                    Be::getService('System', 'AdminLog')->addLog($this->config['name'] . '：删除' . $primaryKey . '为' . $x . '的记录！');
                }
            } else {

                if (isset($this->config['action']['delete']['field'])) {

                    $field = 'block';
                    if (isset($this->config['action']['delete']['field'])) {
                        $field = $this->config['action']['delete']['field'];
                    }

                    $value = 1;
                    if (isset($this->config['action']['delete']['value'])) {
                        $value = $this->config['action']['delete']['value'];
                    }

                    $tuple->load($primaryKeyValue);
                    $tuple->$field = $value;
                    $this->beforeDelete($tuple);
                    $tuple->save();
                    $this->afterDelete($tuple);
                } else {
                    $tuple = Be::newTuple($this->config['table']);
                    $tuple->load($primaryKeyValue);
                    $this->beforeDelete($tuple);
                    $tuple->delete();
                    $this->afterDelete($tuple);
                }

                Be::getService('System', 'AdminLog')->addLog($this->config['name'] . '：删除' . $primaryKey . '为' . $primaryKeyValue . '的记录！');
            }

            Be::getDb()->commit();
        } catch (\Exception $e) {

            Be::getDb()->rollback();
            Response::error($e->getMessage());
        }

        Response::success('删除成功！');
    }


    /**
     * 删除前调用，可抛出异常，事务回滚
     *
     * @param Tuple $tuple 行记录
     * @throws \Exception
     */
    protected function beforeDelete(Tuple $tuple) {}

    /**
     * 删除后调用，可抛出异常，事务回滚
     *
     * @param Tuple $tuple 行记录
     * @throws \Exception
     */
    protected function afterDelete(Tuple $tuple) {}


    /*
     * 导出
     */
    public function export()
    {
        if (!isset($this->config['action']['export'])) {
            Response::end($this->config['name'] . '：导出功能未开启');
        }

        if (!isset($this->config['action']['lists'])) {
            Response::end($this->config['name'] . '：导出功能依赖的列表功能未开启');
        }

        $table = Be::newTable($this->config['table']);

        foreach($this->config['action']['lists']['search'] as $key => $search) {
            $driver = $search['driver'];
            $searchDriver = new $driver($key, $search);
            $searchDriver->buildWhere($table, Request::post());
        }

        $lists = $table->getYieldArrays();

        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename=' . date('YmdHis') . '.csv');
        $handler = fopen('php://output', 'w') or die("can't open php://output");
        fwrite($handler, pack('H*', 'EFBBBF')); // 写入 BOM 头

        $headers = array();
        $fields = $table->getFields();
        foreach ($fields as $field) {
            if ($field['disable']) continue;

            $headers[] = $field['name'];
        }
        fputcsv($handler, $headers);

        $fields = $table->getFields();
        foreach ($lists as &$x) {

            $this->formatField($x, $fields);

            fputcsv($handler, $x);
        }

        fclose($handler) or die("can't close php://output");
        Be::getService('System', 'AdminLog')->addLog($this->config['name'] . '：导出记录！');
    }


    protected function formatField(&$data, $fields) {

        foreach ($fields as $field => $config) {

        }
    }

    protected function formatExtField(&$data, $extFields) {

    }


}

