<?php

namespace Be\Plugin\Curd\FieldItem;


/**
 * 字段 进度条
 */
class FieldItemProgress extends FieldItem
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

        if (!isset($this->ui['progress'][':percentage'])) {
            $this->ui['progress'][':percentage'] = 'scope.row.'.$this->name.'';
        }

        if ($this->url) {
            if (!isset($this->ui['progress']['@click'])) {
                $this->ui['progress']['@click'] = 'fieldClick(\'' . $this->name . '\', scope.row)';
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
