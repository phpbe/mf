<?php

namespace Be\Plugin\Table\Item;


/**
 * 字段 进度条
 */
class TableItemProgress extends TableItem
{

    /**
     * 构造函数
     *
     * @param array $params 参数
     */
    public function __construct($params = [])
    {
        parent::__construct($params);

        if (!isset($this->ui['progress'][':percentage'])) {
            $this->ui['progress'][':percentage'] = 'scope.row.'.$this->name;
        }

        if (!isset($this->ui['progress'][':stroke-width'])) {
            $this->ui['progress'][':stroke-width'] = '16';
        }

        if (!isset($this->ui['progress'][':text-inside'])) {
            $this->ui['progress'][':text-inside'] = 'true';
        }

        if ($this->url) {
            if (!isset($this->ui['progress']['@click'])) {
                $this->ui['progress']['@click'] = 'tableItemClick(\'' . $this->name . '\', scope.row)';
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
        $html .= '<el-progress';
        if (isset($this->ui['progress'])) {
            foreach ($this->ui['progress'] as $k => $v) {
                if ($v === null) {
                    $html .= ' ' . $k;
                } else {
                    $html .= ' ' . $k . '="' . $v . '"';
                }
            }
        }
        $html .= '>';
        $html .= '</el-progress>';
        $html .= '</template>';
        $html .= '</el-table-column>';

        return $html;
    }

}
