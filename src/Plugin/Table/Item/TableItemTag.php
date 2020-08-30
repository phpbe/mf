<?php

namespace Be\Plugin\Table\Item;


/**
 * 字段 标签
 */
class TableItemTag extends TableItem
{

    /**
     * 构造函数
     *
     * @param array $params 参数
     */
    public function __construct($params = [])
    {
        parent::__construct($params);

        if (!isset($this->ui['tag']['size'])) {
            $this->ui['tag']['size'] = isset($params['size']) ? $params['size'] : 'mini';
        }

        if ($this->url) {
            if (!isset($this->ui['tag']['@click'])) {
                $this->ui['tag']['@click'] = 'tableItemClick(\'' . $this->name . '\', scope.row)';
            }
        }
    }

    /**
     * 获取html内容
     *
     * @return string
     */
    public function getHtml()
    {
        $html = '<el-table-column';
        if (isset($this->ui['table-column'])) {
            foreach ($this->ui['table-column'] as $k => $v) {
                if ($v === null) {
                    $html .= ' ' . $k;
                } else {
                    $html .= ' ' . $k . '="' . $v . '"';
                }
            }
        }
        $html .= '>';
        $html .= '<template slot-scope="scope">';
        $html .= '<el-tag';
        if (isset($this->ui['tag'])) {
            foreach ($this->ui['tag'] as $k => $v) {
                if ($v === null) {
                    $html .= ' ' . $k;
                } else {
                    $html .= ' ' . $k . '="' . $v . '"';
                }
            }
        }
        $html .= '>';
        $html .= '{{scope.row.'.$this->name.'}}';
        $html .= '</el-tag>';
        $html .= '</template>';
        $html .= '</el-table-column>';

        return $html;
    }
}