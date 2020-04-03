<?php

namespace Be\Plugin\Lists\Field;


/**
 * 头像履性
 */
class FieldItemAvatar extends FieldItem
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
    }

    /**
     * 编辑
     *
     * @return string | array
     */
    public function getHtml()
    {
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
