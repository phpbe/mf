<?php

namespace Be\Plugin\CurdTree;

use Be\System\Be;
use Be\System\Db\Tuple;
use Be\System\Request;
use Be\System\Response;
use Be\System\Session;
use Be\System\Cookie;

/**
 * 增删改查
 *
 * Class CurdTree
 * @package App\System\AdminTrait
 */
trait CurdTree
{

    public $config = [];

    /*
     * 在子类中必须定义 $config 属性，示例值如下：
    public $config = [

        'name' => '用户角色',

        'table' => 'System.UserRole',

        'lists' => [
            'toolbar' => [
                '新建' => 'create',
            ],

            'action' => [
                '删除' => 'delete',
            ],
        ],

        'edit' => [],

        'block' => [
            'field' => 'block',
            'value' => 1
        ],

        'unblock' => [
            'field' => 'block',
            'value' => 0
        ],

        'delete' => [
            'field' => 'is_delete',
            'value' => 1
        ],
    ];
    */

    /**
     * 列表展示
     */
    public function lists()
    {
        if (!isset($this->config['lists'])) {
            Response::end($this->config['name'] . '：列表功能未开启');
        }

        $app = Be::getRuntime()->getAppName();
        $controller = Be::getRuntime()->getControllerName();

        $table = Be::newTable($this->config['table']);

        $primaryKey = $table->getPrimaryKey();

        if (Request::isPost()) {

            foreach($this->config['search'] as $key => $search) {
                $driver = $search['driver'];
                $searchDriver = new $driver($key, $search);
                $searchDriver->buildWhere($table, Request::post());
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
                    $limit = Be::getConfig('System.Admin')->limit;
                }
            } else {
                Cookie::set($cookieLimitKey, $limit, 86400 * 30);
            }

            $total = $table->count();

            $table->offset($offset)->limit($limit);

            $orderBy = Request::post('sort', $primaryKey);
            $orderByDir = Request::post('order', 'DESC');
            $table->orderBy($orderBy, $orderByDir);

            $lists = $table->getObjects();

            $fields = $table->getFields();
            foreach ($lists as &$x) {
                $this->formatData($x, $fields);
            }

            Response::set('total', $total);
            Response::set('rows', $lists);
            Response::json();
        }

        Response::setTitle($this->config['name'] . '：列表');
        Response::set('table', $table);
        Response::set('config', $this->config);

        Response::display('AdminTrait.Curd.lists');
    }

    /**
     * 编辑
     */
    public function save()
    {
        if (!isset($this->config['edit'])) {
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
                $this->beforeEdit();

                $tuple->bind(Request::post());
                $tuple->save();

                Be::getService('System.AdminLog')->addLog($this->config['name'] . '：编辑' . $primaryKey . '为' . $primaryKeyValue . '的记录！');

                $this->afterEdit($tuple);

                Be::getDb()->commit();
            } catch (\Exception $e) {

                Be::getDb()->rollback();
                Response::error($e->getMessage());
            }

            Response::success('修改成功！');

        } else {

            Response::setTitle($this->config['name'] . '：编辑');
            Response::set('row', $tuple);
            Response::display('AdminTrait.Curd.edit');
        }
    }

    /**
     * 编辑保存前调用，可抛出异常，事务回滚
     *
     * @throws \Exception
     */
    protected function beforeEdit() {}

    /**
     * 编辑保存后调用，可抛出异常，事务回滚
     *
     * @param Tuple $tuple 编辑的行对象
     * @throws \Exception
     */
    protected function afterEdit($tuple) {}


    public function block()
    {
        if (!isset($this->config['block'])) {
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

            $this->beforeBlock();

            $tupleList = null;

            if (is_array($primaryKeyValue)) {

                $tupleList = [];
                foreach ($primaryKeyValue as $x) {

                    $field = 'block';
                    if (isset($this->config['block']['field'])) {
                        $field = $this->config['block']['field'];
                    }

                    $value = 1;
                    if (isset($this->config['block']['value'])) {
                        $value = $this->config['block']['value'];
                    }

                    $tuple->load($primaryKeyValue);
                    $tuple->$field = $value;
                    $tuple->save();

                    $tupleList[] = $tuple;

                    Be::getService('System.AdminLog')->addLog($this->config['name'] . '：禁用' . $primaryKey . '为' . $x . '的记录！');
                }
            } else {

                $field = 'block';
                if (isset($this->config['block']['field'])) {
                    $field = $this->config['block']['field'];
                }

                $value = 1;
                if (isset($this->config['block']['value'])) {
                    $value = $this->config['block']['value'];
                }

                $tuple->load($primaryKeyValue);
                $tuple->$field = $value;
                $tuple->save();

                $tupleList = $tuple;

                Be::getService('System.AdminLog')->addLog($this->config['name'] . '：禁用' . $primaryKey . '为' . $primaryKeyValue . '的记录！');
            }

            $this->afterBlock($tupleList);

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
     * @throws \Exception
     */
    protected function beforeBlock() {}

    /**
     * 禁用后调用，可抛出异常，事务回滚
     *
     * @param array|Tuple $tupleList 行记录数组或行记录
     * @throws \Exception
     */
    protected function afterBlock($tupleList) {}


    public function unblock()
    {
        if (!isset($this->config['unblock'])) {
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

            $this->beforeUnblock();

            $tupleList = null;
            if (is_array($primaryKeyValue)) {

                $tupleList = [];

                foreach ($primaryKeyValue as $x) {

                    $field = 'block';
                    if (isset($this->config['unblock']['field'])) {
                        $field = $this->config['unblock']['field'];
                    }

                    $value = 0;
                    if (isset($this->config['unblock']['value'])) {
                        $value = $this->config['unblock']['value'];
                    }

                    $tuple->load($primaryKeyValue);
                    $tuple->$field = $value;
                    $tuple->save();

                    $tupleList[] = $tuple;

                    Be::getService('System.AdminLog')->addLog($this->config['name'] . '：启用' . $primaryKey . '为' . $x . '的记录！');
                }
            } else {

                $field = 'block';
                if (isset($this->config['unblock']['field'])) {
                    $field = $this->config['unblock']['field'];
                }

                $value = 0;
                if (isset($this->config['unblock']['value'])) {
                    $value = $this->config['unblock']['value'];
                }

                $tuple->load($primaryKeyValue);
                $tuple->$field = $value;
                $tuple->save();

                $tupleList = $tuple;

                Be::getService('System.AdminLog')->addLog($this->config['name'] . '：启用' . $primaryKey . '为' . $primaryKeyValue . '的记录！');
            }

            $this->afterUnblock($tupleList);

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
     * @throws \Exception
     */
    protected function beforeUnblock() {}

    /**
     * 删除后调用，可抛出异常，事务回滚
     *
     * @param array|Tuple $tupleList 行记录数组或行记录
     * @throws \Exception
     */
    protected function afterUnblock($tupleList) {}


    /**
     * 删除
     */
    public function delete()
    {
        if (!isset($this->config['delete'])) {
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

            $this->beforeDelete();

            $tupleList = null;

            if (is_array($primaryKeyValue)) {
                foreach ($primaryKeyValue as $x) {
                    if (isset($this->config['delete']['field'])) {

                        $field = 'block';
                        if (isset($this->config['delete']['field'])) {
                            $field = $this->config['delete']['field'];
                        }

                        $value = 1;
                        if (isset($this->config['delete']['value'])) {
                            $value = $this->config['delete']['value'];
                        }

                        $tuple = Be::newTuple($this->config['table']);
                        $tuple->load($x);
                        $tuple->$field = $value;
                        $tuple->save();

                        $tupleList[] = $tuple;
                    } else {
                        $tuple = Be::newTuple($this->config['table']);
                        $tuple->load($x);
                        $tuple->delete();

                        $tupleList[] = $tuple;
                    }

                    Be::getService('System.AdminLog')->addLog($this->config['name'] . '：删除' . $primaryKey . '为' . $x . '的记录！');
                }
            } else {

                if (isset($this->config['delete']['field'])) {

                    $field = 'block';
                    if (isset($this->config['delete']['field'])) {
                        $field = $this->config['delete']['field'];
                    }

                    $value = 1;
                    if (isset($this->config['delete']['value'])) {
                        $value = $this->config['delete']['value'];
                    }

                    $tuple->load($primaryKeyValue);
                    $tuple->$field = $value;
                    $tuple->save();

                    $tupleList = $tuple;

                } else {

                    $tuple = Be::newTuple($this->config['table']);
                    $tuple->load($primaryKeyValue);
                    $tuple->delete();

                    $tupleList = $tuple;
                }

                Be::getService('System.AdminLog')->addLog($this->config['name'] . '：删除' . $primaryKey . '为' . $primaryKeyValue . '的记录！');
            }

            $this->afterDelete($tupleList);

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
     * @throws \Exception
     */
    protected function beforeDelete() {}

    /**
     * 删除后调用，可抛出异常，事务回滚
     *
     * @param array|Tuple $tupleList 行记录数组或行记录
     * @throws \Exception
     */
    protected function afterDelete($tupleList) {}



}

