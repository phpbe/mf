<?php

namespace Be\Plugin\Toolbar\Item;


/**
 * 工具栏 按钮
 */
class ToolbarItemButton extends ToolbarItem
{


    /**
     * 构造函数
     *
     * @param array $params 参数
     */
    public function __construct($params = [])
    {
        parent::__construct($params);

        if (!isset($this->ui['icon'])) {
            if (isset($params['icon'])) {
                $this->ui['icon'] = $params['icon'];
            }
        }

        if (!isset($this->ui['@click'])) {
            $this->ui['@click'] = 'toolbarItemClick(\'' . $this->name . '\')';
        }

        if (!isset($this->ui[':disabled'])) {
            $this->ui[':disabled'] = '!toolbarItems.' . $this->name . '.enable';
        }

    }

    /**
     * 获取html内容
     *
     * @return string
     */
    public function getHtml()
    {
        $html = '<el-button';
        foreach ($this->ui as $k => $v) {
            if ($v === null) {
                $html .= ' ' . $k;
            } else {
                $html .= ' ' . $k . '="' . $v . '"';
            }
        }
        $html .= '>';
        $html .= $this->label;
        $html .= '</el-button>';

        return $html;
    }
}
