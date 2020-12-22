<?php

namespace Be\Plugin\Table\Item;


/**
 * 字段 链接
 */
class TableItemLink extends TableItem
{


    /**
     * 构造函数
     *
     * @param array $params 参数
     */
    public function __construct($params = [])
    {
        parent::__construct($params);

        if (!isset($this->ui['link']['type'])) {
            if (isset($params['type'])) {
                $this->ui['link']['type'] = $params['type'];
            } else {
                $this->ui['link']['type'] = 'primary';
            }
        }

        if ($this->url) {
            if (!isset($this->ui['link']['@click'])) {
                $this->ui['link']['@click'] = 'tableItemClick(\'' . $this->name . '\', scope.row)';
            }
        } else {
            if (!isset($this->ui['link']['@click'])) {
                $this->ui['link']['@click'] = 'tableItemLinkClick(\'' . $this->name . '\', scope.row)';
            }
        }
    }

    /**
     * 获取html内容
     *
     * @return string
     */
    public function getHtml()
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
        $html .= '<el-link';
        if (isset($this->ui['link'])) {
            foreach ($this->ui['link'] as $k => $v) {
                if ($v === null) {
                    $html .= ' ' . $k;
                } else {
                    $html .= ' ' . $k . '="' . $v . '"';
                }
            }
        }
        $html .= '>';
        $html .= '{{scope.row.'.$this->name.'}}';
        $html .= '</el-link>';
        $html .= '</template>';
        $html .= '</el-table-column>';

        return $html;
    }

    /**
     * 获取 vue data
     *
     * @return false | array
     */
    public function getVueData()
    {
        $vueData = [
            'tableItems' => [
                $this->name => [
                    'url' => $this->url ?? '',
                    'confirm' => $this->confirm === null ? '' : $this->confirm,
                    'target' => $this->target,
                    'postData' => $this->postData,
                ]
            ]
        ];

        if ($this->target == 'dialog') {
            $vueData['tableItems'][$this->name]['dialog'] = $this->dialog;
        } elseif ($this->target == 'drawer') {
            $vueData['tableItems'][$this->name]['drawer'] = $this->drawer;
        }

        return $vueData;
    }

    /**
     * 获取 vue 方法
     *
     * @return false | array
     */
    public function getVueMethods()
    {
        return [
            'tableItemLinkClick' => 'function (name, row) {
                var option = this.tableItems[name];
                option.url = row[name];
                if (option.confirm) {
                    var _this = this;
                    this.$confirm(option.confirm, \'操作确认\', {
                      confirmButtonText: \'确定\',
                      cancelButtonText: \'取消\',
                      type: \'warning\'
                    }).then(function(){
                        _this.tableItemAction(name, option, row);
                    }).catch(function(){});
                } else {
                    this.tableItemAction(name, option, row);
                }
            }'
        ];
    }

}
