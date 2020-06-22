<?php

namespace Be\Plugin\Lists\ListItem;


/**
 * 字段 头像
 */
class ListItemAvatar extends ListItem
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

        $html = '<el-avatar';
        $html .= ' src="' . $this->value . '"';

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

        return $html;
    }

}
