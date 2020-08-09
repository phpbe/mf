<?php

namespace Be\Plugin\Curd;

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
        $tabHtml = '<el-tabs v-model="formData.' . $this->name . '" type="card" @tab-click="tabClick">';
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
     * @param string $dbName
     * @return string
     */
    public function buildSql($dbName = 'master')
    {
        if ($this->newValue !== null) {

            $field = null;
            if (isset($this->option['table'])) {
                $field = $this->option['table'] .'.' . $this->name;
            } else {
                $field = $this->name;
            }

            $db = Be::getDb($dbName);
            return $db->quoteKey($field) . ' = ' . $db->quoteValue($this->newValue);
        }

        return '';
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
                this.formData.'.$this->name.' = tab.name;
                this.gotoPage(1);
            }',
        ];
    }



}
