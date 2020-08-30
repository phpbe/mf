<?php

namespace Be\Plugin\Toolbar\Item;


/**
 * 工具栏 链接
 */
class ToolbarItemLink extends ToolbarItem
{


    /**
     * 构造函数
     *
     * @param array $params 参数
     */
    public function __construct($params = [])
    {
        parent::__construct($params);

        if (!isset($this->ui['link']['type'])) {
            if (isset($params['type'])) {
                $this->ui['link']['type'] = $params['type'];
            } else {
                $this->ui['link']['type'] = 'primary';
            }
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
     * @return string
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
