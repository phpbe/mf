<?php

namespace Be\Mf\Plugin\Operation\Item;


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

        if (!isset($this->ui['size'])) {
            $this->ui['size'] = 'mini';
        }

        if (!isset($this->ui['@click'])) {
            $this->ui['@click'] = 'operationItemClick(\'' . $this->name . '\', scope.row)';
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
        foreach ($this->ui as $k => $v) {
            if ($v === null) {
                $html .= ' ' . $k;
            } else {
                $html .= ' ' . $k . '="' . $v . '"';
            }
        }
        $html .= '>';
        $html .= '<span>' . $this->label . '</span>';
        $html .= '</el-button>';

        return $html;
    }
}
