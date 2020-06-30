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
        $tabHtml = '<el-tabs v-model="searchForm.' . $this->setting['lists']['tab']['name'] . '" @tab-click="tabClick">';
        foreach ($this->setting['lists']['tab']['keyValues'] as $key => $val) {
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
        if (isset($data[$this->name]) && $data[$this->name]) {
            $this->newValue = $data[$this->name];
        }
    }

    /**
     * 查询SQL
     *
     * @return string
     */
    public function buildSql()
    {
        $where = [];
        if ($this->newValue !== null) {

            if (isset($this->option['db'])) {
                $db = Be::getDb($this->option['db']);
            } else {
                $db = Be::getDb();
            }

            if (isset($this->option['table'])) {
                $where = $db->quoteKey($this->option['table']) . '.';
            }

            $field = null;
            if (isset($this->option['table'])) {
                $field = $db->quoteKey($this->option['table']) . '.' . $db->quoteKey($this->name);
            } else {
                $field = $db->quoteKey($this->name);
            }

            $where[] =  $field . '=' . $db->quoteValue($this->newValue);
        }

        return $where;
    }

}
