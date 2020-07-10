<?php

namespace Be\Plugin\Curd\OperationItem;

use Be\System\Be;

/**
 * 搜索项 布尔值
 */
class OperationItemButtonDropDownItem extends OperationItem
{

    private $key = 0;

    /**
     * 构造函数
     *
     * @param array $params 参数
     * @param object $tuple 行数据
     */
    public function __construct($params = [], $tuple = null)
    {
        parent::__construct($params, $tuple);

        if (isset($params['key'])) {
            $this->key = $params['key'];
        }
    }

    /**
     * 获取html内容
     *
     * @return string | array
     */
    public function getHtml()
    {
        $html = '<el-menu-item key="' . $this->key . '">';
        if (isset($menu['ui']['icon'])) {
            $html .= ' <el-icon type="' . $menu['ui']['icon'] . '"></el-icon>';
        }
        $html .= $menu['value'];
        $html .= '</el-menu-item>';

        return $html;
    }


}


