<?php

namespace Be\Plugin\Lists\Field;


/**
 * 搜索项 布尔值
 */
class FieldItemSwitch extends FieldItem
{


    /**
     * 构造函数
     *
     * @param array $params 参数
     */
    public function __construct($params = [])
    {
        parent::__construct($params);

        if (!isset($this->ui['switch']['defaultChecked'])) {
            if ($this->value) {
                $this->ui['switch']['defaultChecked'] = null;
            }
        }

        // 可点击操作
        if (!isset($this->url)) {
            $this->ui['switch']['@change'] = 'fieldAction';
        }
    }

    /**
     * 编辑
     *
     * @return string | array
     */
    public function getHtml()
    {
        $html = '<a-switch';
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
        $html .= '</a-switch>';

        return $html;
    }

}
