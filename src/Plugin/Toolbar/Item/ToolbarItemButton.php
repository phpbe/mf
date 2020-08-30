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

        if (!isset($this->ui['button']['icon'])) {
            if (isset($params['icon'])) {
                $this->ui['button']['icon'] = $params['icon'];
            }
        }

        if (!isset($this->ui['button']['@click'])) {
            $this->ui['button']['@click'] = 'toolbarClick(\'' . $this->name . '\')';
        }

        if (!isset($this->ui['button'][':disabled'])) {
            $this->ui['button'][':disabled'] = '!toolbar.' . $this->name . '.enable';
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
        $html .= '</el-button>';

        return $html;
    }
}
