<?php

namespace Be\Plugin\Lists\Field;


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

        $html = '<a-avatar';
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
        $html .= '</a-avatar>';

        return $html;
    }

}
