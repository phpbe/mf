<?php

namespace Be\Plugin\Editor\EditorItem;

use Be\System\Exception\PluginException;

/**
 * 搜索项 整型
 */
class EditorItemInputNumberInt extends EditorItem
{

    /**
     * 获取html内容
     *
     * @return string | array
     */
    public function getHtml()
    {
        if (!isset($this->ui['input-number'][':step'])) {
            $this->ui['input-number'][':step'] = '1';
        }

        if (!isset($this->ui['input-number'][':formatter'])) {
            $this->ui['input-number'][':formatter'] = 'value => isNaN(value)||value==\'\'?0:parseInt(value)';
        }

        if (!isset($this->ui['input-number']['v-decorator'])) {
            $this->ui['input-number']['v-decorator'] = '[\''.$this->name.'\']';
        }

        $html = '<el-form-item';
        foreach ($this->ui['form-item'] as $k => $v) {
            if ($v === null) {
                $html .= ' '.$k;
            } else {
                $html .= ' '.$k.'="' . $v . '"';
            }
        }
        $html .= '>';

        $html .= '<el-input-number';
        if (isset($this->ui['input-number'])) {
            foreach ($this->ui['input-number'] as $k => $v) {
                if ($v === null) {
                    $html .= ' '.$k;
                } else {
                    $html .= ' '.$k.'="' . $v . '"';
                }
            }
        }
        $html .= '>';
        $html .= '</el-input-number>';

        $html .= '</el-form-item>';
        return $html;
    }

    /**
    /**
     * 提交处理
     *
     * @param $data
     * @throws PluginException
     */
    public function submit($data)
    {
        if (isset($data[$this->field])) {
            $newValue =  $data[$this->field];

            if (!is_numeric($newValue)) {
                throw new PluginException('参数 ' . $this->label . ' (' . $this->field . ') 不是合法的数字');
            }

            $this->newValue = (int) $newValue;
        }

    }


}
