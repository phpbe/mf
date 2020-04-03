<?php

namespace Be\Plugin\Lists\Search;


/**
 * 搜索项 布尔值
 */
class SearchItemInput extends SearchItem
{


    /**
     * 构造函数
     *
     * @param array $params 参数
     */
    public function __construct($params = array())
    {
        parent::__construct($params);

        if (!isset($this->ui['input']['v-decorator'])) {
            $this->ui['input']['v-decorator'] = '[\''.$this->name.'\']';
        }
    }

    /**
     * 编辑
     *
     * @return string | array
     */
    public function getHtml()
    {
        $html = '<a-form-item';
        foreach ($this->ui['form-item'] as $k => $v) {
            if ($v === null) {
                $html .= ' '.$k;
            } else {
                $html .= ' '.$k.'="' . $v . '"';
            }
        }
        $html .= '>';

        $html .= '<a-input';
        if (isset($this->ui['input'])) {
            foreach ($this->ui['input'] as $k => $v) {
                if ($v === null) {
                    $html .= ' ' . $k;
                } else {
                    $html .= ' ' . $k . '="' . $v . '"';
                }
            }
        }
        $html .= '>';
        $html .= '</a-input>';

        $html .= '</a-form-item>';
        return $html;
    }
}
