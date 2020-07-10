<?php

namespace Be\Plugin\Curd\OperationItem;


/**
 * 搜索项 布尔值
 */
class OperationItemLink extends OperationItem
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
    }

    /**
     * 编辑
     *
     * @return string | array
     */
    public function getHtml()
    {
        $html = '<el-link';
        $html .= ' @click="operationAction(\'' . $this->name . '\', scope.row)"';
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
