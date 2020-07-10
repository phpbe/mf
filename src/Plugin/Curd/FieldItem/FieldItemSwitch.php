<?php

namespace Be\Plugin\Curd\FieldItem;


/**
 * 字段 开关
 */
class FieldItemSwitch extends FieldItem
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

        if ($this->url) {
            if (!isset($this->ui['switch']['@click'])) {
                $this->ui['switch']['@click'] = 'fieldClick(\'' . $this->name . '\', scope.row)';
            }

            if ($params['task'] =='toggle' && !isset($this->postData['field'])) {
                $this->postData['field'] = $this->name;
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
        if (!isset($this->ui['switch'][':defaultChecked'])) {
            $this->ui['switch'][':defaultChecked'] = 'scope.row.' . $this->name;
        }

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
        $html .= '<el-switch';
        if (isset($this->ui['switch'])) {
            foreach ($this->ui['switch'] as $k => $v) {
                if ($v === null) {
                    $html .= ' ' . $k;
                } else {
                    $html .= ' ' . $k . '="' . $v . '"';
                }
            }
        }
        $html .= '>';
        $html .= '</el-switch>';
        $html .= '</template>';
        $html .= '</el-table-column>';

        return $html;
    }

}
