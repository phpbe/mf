<?php

namespace Be\Plugin\Lists\FieldItem;


/**
 * 字段 开关
 */
class FieldItemSwitch extends FieldItem
{

    /**
     * 获取html内容
     *
     * @return string | array
     */
    public function getHtml()
    {
        if (!isset($this->ui['switch']['defaultChecked'])) {
            if ($this->value) {
                $this->ui['switch']['defaultChecked'] = null;
            }
        }

        // 可点击操作
        if (isset($this->url)) {
            $option = null;
            if (isset($this->option)) {
                $option = json_encode($this->option);
            } else {
                $option = '{}';
            }

            $data = null;
            if (isset($this->data)) {
                $data = json_encode($this->data);
            } else {
                $data = '{}';
            }

            $this->ui['switch']['@change'] = 'fieldAction(\''.htmlspecialchars($this->label).'\', \''.$this->url.'\', '.$option.', '.$data.')';
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
