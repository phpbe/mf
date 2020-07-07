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

        if (!isset($this->ui['link']['href'])) {
            if (isset($params['href'])) {
                $this->ui['link']['href'] = $params['href'];
            } elseif ($this->url !== null) {
                $this->ui['link']['href'] = $this->url;
            }
        }

        if (!isset($this->ui['link']['target']) && isset($params['target'])) {
            $this->ui['link']['target'] = $params['target'];
        }
    }

    /**
     * 获取html内容
     *
     * @return string | array
     */
    public function getHtml()
    {
        $html = '<a';
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
        $html .= $this->value;
        $html .= '</a>';

        return $html;
    }
}
