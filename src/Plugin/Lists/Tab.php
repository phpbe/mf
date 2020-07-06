<?php

namespace Be\Plugin\Lists;

use Be\Plugin\Lists\Item;
use Be\System\Be;

/**
 * 搜索项驱动
 */
class Tab extends Item
{

    protected $newValue = null; // 新值

    /**
     * 获取html内容
     *
     * @return string | array
     */
    public function getHtml()
    {
        $tabHtml = '<el-tabs v-model="searchForm.' . $this->name . '" @tab-click="tabClick">';
        foreach ($this->keyValues as $key => $val) {
            $tabHtml .= '<el-tab-pane label="' . $val . '" name="' . $key . '"></el-tab-pane>';
        }

        $tabHtml .= '</el-tabs>';
        return $tabHtml;
    }

    /**
     * 提交处理
     *
     * @param $data
     * @throws \Exception
     */
    public function submit($data)
    {
        if (isset($data[$this->name])) {
            $this->newValue = $data[$this->name];
        }
    }

    /**
     * 查询SQL
     *
     * @return array
     */
    public function buildSql()
    {
        $wheres = [];
        if ($this->newValue !== null) {

            $field = null;
            if (isset($this->option['table'])) {
                $field = $this->option['table'] .'.' . $this->name;
            } else {
                $field = $this->name;
            }

            $wheres[] = [$field,  $this->newValue];
        }

        return $wheres;
    }


    /**
     * 获取 vue 方法
     *
     * @return false | array
     */
    public function getVueMethods()
    {
        return [
            'tabClick' => 'function (tab, event) {
                this.searchForm.'.$this->name.' = tab.name;
                this.gotoPage(1);
            }',
        ];
    }



}
