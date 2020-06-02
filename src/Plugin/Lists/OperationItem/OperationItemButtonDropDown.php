<?php

namespace Be\Plugin\Lists\OperationItem;

use Be\System\Be;

/**
 * 搜索项 布尔值
 */
class OperationItemButtonDropDown extends OperationItem
{

    protected $menus = []; // 下拉菜单

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
                $this->menus = $tmpMenus;
            }

            foreach ($this->menus as &$m) {

                if (isset($m['label'])) {
                    $label = $m['label'];
                    if (is_callable($label)) {
                        $m['label'] = $label();
                    }
                }

                if (isset($m['value'])) {
                    $value = $m['value'];
                    if (is_callable($value)) {
                        $m['value'] = $value();
                    }
                }

                if (isset($m['url'])) {
                    $url = $m['url'];
                    if (is_callable($url)) {
                        $m['url'] = $url();
                    }
                } else {
                    if (isset($m['task'])) {
                        $task = $m['task'];
                        if (is_callable($task)) {
                            $task = $task();
                        }

                        $runtime = Be::getRuntime();
                        $m['url'] = url($runtime->getAppName() . '.' . $runtime->getControllerName() . '.' . $runtime->getActionName(), ['task' => $task]);
                    }
                }

                if (isset($m['ui'])) {
                    $ui = $m['ui'];
                    if (is_callable($ui)) {
                        $m['ui'] = $ui();
                    }
                }

                if (isset($m['option'])) {
                    $option = $m['option'];
                    if (is_callable($option)) {
                        $m['option'] = $option();
                    }
                }

                if (isset($m['data'])) {
                    $data = $m['data'];
                    if (is_callable($data)) {
                        $m['data'] = $data();
                    }
                }
            }
        }
    }

    /**
     * 获取html内容
     *
     * @return string | array
     */
    public function getHtml()
    {
        $html = '<a-dropdown';
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
            $html .= '<a-menu slot="overlay" @click="operationButtonDropDownClick(e, \'' . $this->name . '\')">';
            $i = 0;
            foreach ($this->menus as $menu) {
                $html .= '<a-menu-item key="' . $i . '">';

                if (isset($menu['ui']['icon'])) {
                    $html .= ' <a-icon type="' . $menu['ui']['icon'] . '"></a-icon>';
                }

                $html .= $menu['value'];
                $html .= '</a-menu-item>';

                $i++;
            }
            $html .= '</a-menu>';
        }

        $html .= $this->value;
        $html .= '<a-button> ' . $this->value . ' <a-icon type="down"></a-icon></a-button>';

        $html .= '</a-dropdown>';
        return $html;
    }


    /**
     * 获取 vue data
     *
     * @return false | array
     */
    public function getVueData()
    {
        return [
            'operation' => [
                $this->name => [
                    'menus' => $this->menus,
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
                var oMenu = this.operation[name].menus[e.key];
                this.operationAction(oMenu);
            }',
        ];
    }



}


