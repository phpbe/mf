<?php

namespace Be\Mf\Plugin\Table\Item;


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
                var sUrl = option.url ? option.url:row[name]; 
                if (option.confirm) {
                    var _this = this;
                    this.$confirm(option.confirm, \'操作确认\', {
                      confirmButtonText: \'确定\',
                      cancelButtonText: \'取消\',
                      type: \'warning\'
                    }).then(function(){
                         switch (option.target) {
                            case "self":
                            case "_self":
                                window.location.href = sUrl;
                            case "dialog":
                                _this.dialog.title = option.dialog.title;
                                _this.dialog.width = option.dialog.width;
                                _this.dialog.height = option.dialog.height;
                                _this.dialog.visible = true;
                                setTimeout(function () {
                                    document.getElementById("frame-dialog").src = sUrl;
                                }, 50);
                                break;
                            case "drawer":
                                _this.drawer.title = option.drawer.title;
                                _this.drawer.width = option.drawer.width;
                                _this.drawer.visible = true;
                                setTimeout(function () {
                                    document.getElementById("frame-drawer").src = sUrl;
                                }, 50);
                                break;
                            case "blank":
                            case "_blank":
                            default:
                                window.open(sUrl);
                        }
                    }).catch(function(){});
                } else {
                    switch (option.target) {
                        case "self":
                        case "_self":
                            window.location.href = sUrl;
                        case "dialog":
                            this.dialog.title = option.dialog.title;
                            this.dialog.width = option.dialog.width;
                            this.dialog.height = option.dialog.height;
                            this.dialog.visible = true;
                            setTimeout(function () {
                                document.getElementById("frame-dialog").src = sUrl;
                            }, 50);
                            break;
                        case "drawer":
                            this.drawer.title = option.drawer.title;
                            this.drawer.width = option.drawer.width;
                            this.drawer.visible = true;
                            setTimeout(function () {
                                document.getElementById("frame-drawer").src = sUrl;
                            }, 50);
                            break;
                        case "blank":
                        case "_blank":
                        default:
                            window.open(sUrl);
                    }
                }
            }'
        ];
    }

}
