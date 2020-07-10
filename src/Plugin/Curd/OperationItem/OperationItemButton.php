<?php

namespace Be\Plugin\Curd\OperationItem;


/**
 * 搜索项 布尔值
 */
class OperationItemButton extends OperationItem
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

        if (!isset($this->ui['button']['size'])) {
            $this->ui['button']['size'] = isset($params['size']) ? $params['size'] : 'mini';
        }

        if (!isset($this->ui['button']['type']) && isset($params['type'])) {
            $this->ui['button']['type'] = $params['type'];
        }

        if (!isset($this->ui['button']['icon']) && isset($params['icon'])) {
            $this->ui['button']['icon'] = $params['icon'];
        }

        if (!isset($this->ui['button']['@click'])) {
            $this->ui['button']['@click'] = 'operationClick(\'' . $this->name . '\', scope.row)';
        }
    }


    /**
     * 编辑
     *
     * @return string | array
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
        $html .= '<span>' . $this->label . '</span>';
        $html .= '</el-button>';

        return $html;
    }
}
