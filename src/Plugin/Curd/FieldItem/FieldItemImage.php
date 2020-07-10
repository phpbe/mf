<?php

namespace Be\Plugin\Curd\FieldItem;


/**
 * 字段 图片
 */
class FieldItemImage extends FieldItem
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

        if (!isset($this->ui['image'][':src'])) {
            $this->ui['image'][':src'] = 'scope.row.'.$this->name.'';
        }

        if ($this->url) {
            if (!isset($this->ui['image']['@click'])) {
                $this->ui['image']['@click'] = 'fieldClick(\'' . $this->name . '\', scope.row)';
            }
        }
    }

    /**
     * 获取html内容
     *
     * @return string | array
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
        $html .= '<el-image';
        if (isset($this->ui['image'])) {
            foreach ($this->ui['image'] as $k => $v) {
                if ($v === null) {
                    $html .= ' ' . $k;
                } else {
                    $html .= ' ' . $k . '="' . $v . '"';
                }
            }
        }
        $html .= '>';
        $html .= '</el-image>';
        $html .= '</template>';
        $html .= '</el-table-column>';

        return $html;
    }

}
