<?php

namespace Be\Plugin\Curd\OperationItem;


/**
 * 操作项 图标
 */
class OperationItemIcon extends OperationItem
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

        if (!isset($this->ui['icon']['@click'])) {
            $this->ui['icon']['@click'] = 'operationClick(\'' . $this->name . '\', scope.row)';
        }
    }

    /**
     * 获取HTML内容
     *
     * @return string
     */
    public function getHtml()
    {
        $html = '<el-icon';
        if (isset($this->ui['icon'])) {
            foreach ($this->ui['icon'] as $k => $v) {
                if ($v === null) {
                    $html .= ' ' . $k;
                } else {
                    $html .= ' ' . $k . '="' . $v . '"';
                }
            }
        }
        $html .= '>';
        $html .= '</el-icon>';

        return $html;
    }
}
