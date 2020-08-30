<?php

namespace Be\Plugin\Table\Item;


/**
 * 字段 头像
 */
class TableItemAvatar extends TableItem
{

    /**
     * 构造函数
     *
     * @param array $params 参数
     */
    public function __construct($params = [])
    {
        parent::__construct($params);

        if (!isset($this->ui['avatar']['shape'])) {
            $this->ui['avatar']['shape'] = 'square';
        }

        if (!isset($this->ui['avatar'][':src'])) {
            $this->ui['avatar'][':src'] = 'scope.row.'.$this->name.'';
        }

        if ($this->url) {
            if (!isset($this->ui['avatar']['@click'])) {
                $this->ui['avatar']['@click'] = 'tableItemClick(\'' . $this->name . '\', scope.row)';
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
        $html .= '<el-avatar';
        if (isset($this->ui['avatar'])) {
            foreach ($this->ui['avatar'] as $k => $v) {
                if ($v === null) {
                    $html .= ' ' . $k;
                } else {
                    $html .= ' ' . $k . '="' . $v . '"';
                }
            }
        }
        $html .= '>';
        $html .= '</el-avatar>';
        $html .= '</template>';
        $html .= '</el-table-column>';

        return $html;

    }

}
