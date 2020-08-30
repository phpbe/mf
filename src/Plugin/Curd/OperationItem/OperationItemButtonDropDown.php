<?php

namespace Be\Plugin\Curd\OperationItem;

use Be\System\Be;

/**
 * 操作项 下拉菜单
 */
class OperationItemButtonDropDown extends OperationItem
{

    public $menus = []; // 下拉菜单


    /**
     * 构造函数
     *
     * @param array $params 参数
     */
    public function __construct($params = [])
    {
        parent::__construct($params);

        if (!isset($this->ui['dropdown']['@command'])) {
            $this->ui['dropdown']['@command'] = 'operationButtonDropDownClick';
        }

        if (!isset($this->ui['button']['size'])) {
            $this->ui['button']['size'] = isset($params['size']) ? $params['size'] : 'mini';
        }

        if (!isset($this->ui['dropdown-menu']['slot'])) {
            $this->ui['dropdown-menu']['slot'] = 'dropdown';
        }

        if (isset($params['menus'])) {
            $menus = $params['menus'];

            $tmpMenus = null;
            if ($menus instanceof \Closure) {
                $tmpMenus = $menus();
            } else {
                $tmpMenus = $menus;
            }

            if (is_array($tmpMenus)) {
                $index = 0;
                $newMenus = [];
                foreach ($tmpMenus as $tmpMenu) {
                    $tmpMenu['parentName'] = $this->name;
                    $tmpMenu['index'] = $index++;
                    $newMenus[] = new OperationItemButtonDropDownMenu($tmpMenu);
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
        $html = '<el-dropdown';
        if (isset($this->ui['dropdown'])) {
            foreach ($this->ui['dropdown'] as $k => $v) {
                if ($v === null) {
                    $html .= ' ' . $k;
                } else {
                    $html .= ' ' . $k . '="' . $v . '"';
                }
            }
        }
        $html .= '>';

        $html .= '<el-button';
        if (isset($this->ui['button'])) {
            foreach ($this->ui['button'] as $k => $v) {
                if ($v === null) {
                    $html .= ' ' . $k;
                } else {
                    $html .= ' ' . $k . '="' . $v . '"';
                }
            }
        }
        $html .= '>';
        $html .= $this->label;
        $html .= '<i class="el-icon-arrow-down el-icon--right"></i>';
        $html .= '</el-button>';

        if (count($this->menus)) {
            $html .= '<el-dropdown-menu';
            if (isset($this->ui['dropdown-menu'])) {
                foreach ($this->ui['dropdown-menu'] as $k => $v) {
                    if ($v === null) {
                        $html .= ' ' . $k;
                    } else {
                        $html .= ' ' . $k . '="' . $v . '"';
                    }
                }
            }
            $html .= '>';

            foreach ($this->menus as $menu) {
                $html .= $menu->getHtml();
            }
            $html .= '</el-dropdown-menu>';
        }

        $html .= '</el-dropdown>';
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
                'enable' => true,
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
                    'enable' => true,
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
            'operationButtonDropDownClick' => 'function (command) {
                var option = this.operation[command.name].menus[command.index];
                this.operationAction(command.name, option, command.row);
            }',
            'operationButtonDropDownMenuCommand' => 'function (name, index, row) {
                return {"name": name, "index": index, "row": row};
            }',
        ];
    }


}


