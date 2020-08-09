<?php

namespace Be\Plugin\Curd\OperationItem;

use Be\System\Be;

/**
 * 搜索项 布尔值
 */
class OperationItemButtonDropDown extends OperationItem
{

    public $menus = []; // 下拉菜单

    /**
     * 构造函数
     *
     * @param array $params 参数
     * @param object $tuple 行数据
     */
    public function __construct($params = [], $tuple = null)
    {
        parent::__construct($params, $tuple);

        if (isset($params['menus'])) {
            $menus = $params['menus'];

            $tmpMenus = null;
            if (is_callable($menus)) {
                $tmpMenus = $menus();
            } else {
                $tmpMenus = $menus;
            }

            if (is_array($tmpMenus)) {
                $i = 0;
                $newMenus = [];
                foreach ($tmpMenus as $tmpMenu) {
                    $tmpMenu['key'] = $i++;
                    $newMenus[] = new OperationItemButtonDropDownItem($tmpMenu);
                }
                $this->menus = $newMenus;
            }
        }
    }

    /**
     * 获取html内容
     *
     * @return string
     */
    public function getHtml()
    {
        $html = '<el-table-column';
        if (isset($this->ui['table-column'])) {
            foreach ($this->ui['table-column'] as $k => $v) {
                if ($v === null) {
                    $html .= ' ' . $k;
                } else {
                    $html .= ' ' . $k . '="' . $v . '"';
                }
            }
        }
        $html .= '>';
        $html .= '<template slot-scope="scope">';

        $html .= '<el-dropdown';
        if (isset($this->ui['dropdown'])) {
            foreach ($this->ui['dropdown'] as $k => $v) {
                if ($k == 'icon') {
                    continue;
                }

                if ($v === null) {
                    $html .= ' ' . $k;
                } else {
                    $html .= ' ' . $k . '="' . $v . '"';
                }
            }
        }
        $html .= '>';

        if (count($this->menus)) {
            $html .= '<el-menu slot="overlay" @click="operationButtonDropDownClick(e, \'' . $this->name . '\')">';
            foreach ($this->menus as $menu) {
                $html .= $menu->getHtml();
            }
            $html .= '</el-menu>';
        }

        $html .= $this->label;
        $html .= '<el-button> ' . $this->label . ' <el-icon type="down"></el-icon></el-button>';
        $html .= '</el-dropdown>';
        $html .= '</template>';
        $html .= '</el-table-column>';

        return $html;
    }


    /**
     * 获取 vue data
     *
     * @return false | array
     */
    public function getVueData()
    {
        $menus = [];
        foreach ($this->menus as $menu) {
            $m = [
                'url' => $menu->url,
                'target' => $menu->target,
                'postData' => $menu->postData,
            ];

            if ($menu->target == 'dialog') {
                $m['dialog'] = $menu->dialog;
            } elseif ($menu->target == 'drawer') {
                $m['drawer'] = $menu->drawer;
            }

            $menus[] = $m;
        }

        return [
            'operation' => [
                $this->name => [
                    'menus' => $menus,
                ]
            ]
        ];
    }


    /**
     * 获取 vue 方法
     *
     * @return false | array
     */
    public function getVueMethods()
    {
        return [
            'operationButtonDropDownClick' => 'function (e, name) {
                var option = this.operation[name].menus[e.key];
                this.operationAction(option);
            }',
        ];
    }



}


