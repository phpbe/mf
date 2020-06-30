<?php

namespace Be\Plugin\Lists\FieldItem;


/**
 * 字段 头像
 */
class FieldItemAvatar extends FieldItem
{

    /**
     * 获取html内容
     *
     * @return string | array
     */
    public function getHtml()
    {
        if (!isset($this->ui['avatar']['shape'])) {
            $this->ui['avatar']['shape'] = 'square';
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
        $html .= '<el-avatar';
        $html .= ' src="{{scope.row.'.$this->name.'}}"';
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
