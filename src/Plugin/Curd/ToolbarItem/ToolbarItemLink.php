<?php

namespace Be\Plugin\Curd\ToolbarItem;


/**
 * 工具栏 链接
 */
class ToolbarItemLink extends ToolbarItem
{


    /**
     * 构造函数
     *
     * @param array $params 参数
     * @param object $tuple 行数据
     */
    public function __construct($params = [], $tuple = null)
    {
        parent::__construct($params, $tuple);

        if (!isset($this->ui['link']['type']) && isset($params['type'])) {
            $this->ui['link']['type'] = $params['type'];
        }

        if (!isset($this->ui['link']['icon']) && isset($params['icon'])) {
            $this->ui['link']['icon'] = $params['icon'];
        }

        if (isset($this->ui['link']['href'])) {
            unset($this->ui['link']['href']);
        }

        if (!isset($this->ui['link']['@click'])) {
            $this->ui['link']['@click'] = 'toolbarClick(\'' . $this->name . '\')';
        }
    }

    /**
     * 获取html内容
     *
     * @return string | array
     */
    public function getHtml()
    {
        $html = '<el-link';
        if (isset($this->ui['link'])) {
            foreach ($this->ui['link'] as $k => $v) {
                if ($v === null) {
                    $html .= ' ' . $k;
                } else {
                    $html .= ' ' . $k . '="' . $v . '"';
                }
            }
        }
        $html .= '>';
        $html .= $this->label;
        $html .= '</el-link>';

        return $html;
    }
}
