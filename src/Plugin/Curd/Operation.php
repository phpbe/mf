<?php

namespace Be\Plugin\Curd;

/**
 * 字段驱动
 */
class Operation extends Item
{

    public $position = 'right';

    /**
     * 构造函数
     *
     * @param array $params 参数

     */
    public function __construct($params = [])
    {
        parent::__construct($params);

        if (isset($params['position'])) {
            $position = $params['position'];
            if (is_callable($position)) {
                $this->position = $position();
            } else {
                $this->position = $position;
            }

            if (!in_array($this->position, ['left', 'right'])) {
                $this->position = 'right';
            }
        }

        if (!isset($this->ui['table-column']['prop'])) {
            $this->ui['table-column']['prop'] = $this->name;
        }

        if (!isset($this->ui['table-column']['label'])) {
            $this->ui['table-column']['label'] = $this->label;
        }

        if (!isset($this->ui['table-column']['width']) && isset($params['width'])) {
            $this->ui['table-column']['width'] = $params['width'];
        }

        if (!isset($this->ui['table-column']['align'])) {
            if (isset($params['align'])) {
                $this->ui['table-column']['align'] = $params['align'];
            } else {
                $this->ui['table-column']['align'] = 'center';
            }
        }

        if (!isset($this->ui['table-column']['header-align'])) {
            if (isset($params['header-align'])) {
                $this->ui['table-column']['header-align'] = $params['header-align'];
            } else {
                $this->ui['table-column']['header-align'] = $this->ui['table-column']['align'];
            }
        }
    }

    /**
     * 编辑
     *
     * @return string | array
     */
    public function getHtmlBefore()
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
        return $html;
    }

    /**
     * 编辑
     *
     * @return string | array
     */
    public function getHtmlAfter()
    {
        $html = '</template>';
        $html .= '</el-table-column>';

        return $html;
    }

}