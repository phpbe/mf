<?php

namespace Be\Plugin\Detail\Item;


/**
 * 明细 进度条
 */
class DetailItemProgress extends DetailItem
{

    /**
     * 构造函数
     *
     * @param array $params 参数
     * @param array $row 数据对象
     */
    public function __construct($params = [], $row = [])
    {
        parent::__construct($params, $row);

        if (!isset($this->ui['progress'][':percentage'])) {
            $this->ui['progress'][':percentage'] = $this->value;
        }
    }

    /**
     * 获取html内容
     *
     * @return string
     */
    public function getHtml()
    {
        $html = '<el-form-item';
        foreach ($this->ui['form-item'] as $k => $v) {
            if ($v === null) {
                $html .= ' '.$k;
            } else {
                $html .= ' '.$k.'="' . $v . '"';
            }
        }
        $html .= '>';
        $html .= '<el-progress';
        if (isset($this->ui['progress'])) {
            foreach ($this->ui['progress'] as $k => $v) {
                if ($v === null) {
                    $html .= ' ' . $k;
                } else {
                    $html .= ' ' . $k . '="' . $v . '"';
                }
            }
        }
        $html .= '>';
        $html .= '</el-progress>';
        $html .= '</el-form-item>';
        return $html;
    }

}
