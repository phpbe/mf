<?php

namespace Be\Plugin\Table\Item;


/**
 * 字段 开关
 */
class TableItemSwitch extends TableItem
{

    /**
     * 构造函数
     *
     * @param array $params 参数
     */
    public function __construct($params = [])
    {
        parent::__construct($params);

        if ($this->url) {
            if (!isset($this->ui['switch']['@change'])) {
                $this->ui['switch']['@change'] = 'tableItemClick(\'' . $this->name . '\', scope.row)';
            }

            if (!isset($this->postData['field'])) {
                $this->postData['field'] = $this->name;
            }
        }

        if (!isset($this->ui['switch']['active-value'])) {
            $this->ui['switch']['active-value'] = '1';
        }

        if (!isset($this->ui['switch']['inactive-value'])) {
            $this->ui['switch']['inactive-value'] = '0';
        }

        if (!isset($this->ui['switch']['v-model'])) {
            $this->ui['switch']['v-model'] = 'tableData[scope.$index].' . $this->name; //'scope.row.' . $this->name;
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
