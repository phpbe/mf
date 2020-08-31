<?php

namespace Be\Plugin\Operation\Item;


/**
 * 操作项 按钮
 */
class OperationItemButton extends OperationItem
{


    /**
     * 构造函数
     *
     * @param array $params 参数
     */
    public function __construct($params = [])
    {
        parent::__construct($params);

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
            $this->ui['button']['@click'] = 'operationItemClick(\'' . $this->name . '\', scope.row)';
        }
    }


    /**
     * 编辑
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
        $html .= '<span>' . $this->label . '</span>';
        $html .= '</el-button>';

        return $html;
    }
}
