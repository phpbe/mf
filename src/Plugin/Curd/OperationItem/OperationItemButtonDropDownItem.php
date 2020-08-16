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
     */
    public function __construct($params = [])
    {
        parent::__construct($params);

        if (isset($params['key'])) {
            $this->key = $params['key'];
        }
    }

    /**
     * 获取html内容
     *
     * @return string
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


