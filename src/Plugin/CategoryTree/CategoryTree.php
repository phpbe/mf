<?php

namespace Be\Mf\Plugin\CategoryTree;

use Be\F\Db\Table;
use Be\Mf\Be;
use Be\Mf\Plugin\Detail\Item\DetailItemAvatar;
use Be\Mf\Plugin\Detail\Item\DetailItemCustom;
use Be\Mf\Plugin\Detail\Item\DetailItemImage;
use Be\Mf\Plugin\Detail\Item\DetailItemProgress;
use Be\Mf\Plugin\Detail\Item\DetailItemSwitch;
use Be\Mf\Plugin\Detail\Item\DetailItemText;
use Be\Mf\Plugin\Form\Item\FormItemDatePickerMonthRange;
use Be\Mf\Plugin\Form\Item\FormItemDatePickerRange;
use Be\Mf\Plugin\Form\Item\FormItemHidden;
use Be\Mf\Plugin\Form\Item\FormItemInput;
use Be\Mf\Plugin\Form\Item\FormItemTimePickerRange;
use Be\Mf\Plugin\Table\Item\TableItemAvatar;
use Be\Mf\Plugin\Table\Item\TableItemCustom;
use Be\Mf\Plugin\Table\Item\TableItemImage;
use Be\Mf\Plugin\Table\Item\TableItemProgress;
use Be\Mf\Plugin\Table\Item\TableItemSwitch;
use Be\Mf\Plugin\PluginException;
use Be\Mf\Plugin\Driver;

/**
 * 增删改查
 *
 * Class CategoryTree
 * @package Be\Mf\Plugin
 */
class CategoryTree extends Driver
{

    /**
     * 配置项
     *
     * @param array $setting
     * @return Driver
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
            $task = Be::getRequest()->request('task', 'display');
        }

        if (method_exists($this, $task)) {
            $this->$task();
        }
    }

    /**
     * 列表展示
     *
     */
    public function display()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();


        $theme = null;
        if (isset($this->setting['theme'])) {
            $theme = $this->setting['theme'];
        }
        $response->display('Plugin.CategoryTree.display', $theme);
        $response->createHistory();

    }

    public function save() {

    }

    /**
     * 删除
     *
     */
    public function delete()
    {
    }


}

