<be-head>
    <style type="text/css">
        .el-table__row .el-divider__text, .el-link {font-size: 12px;}
    </style>
</be-head>

<be-center>
    <?php
    $primaryKey = $this->table->getPrimaryKey();

    $formData = [];
    $vueData = [];
    $vueMethods = [];

    $toolbarItemDriverNames = [];
    ?>
    <div id="app" v-cloak>

        <el-form :inline="true" size="small">

            <?php
            $tabHtml = '';
            $tabPosition = 'BeforeSearch';
            if (isset($this->setting['lists']['tab'])) {
                $driver = new \Be\Plugin\Curd\Tab($this->setting['lists']['tab']);
                $tabHtml = $driver->getHtml();
                if (isset($this->setting['lists']['tab']['position'])) {
                    $tabPosition = $this->setting['lists']['tab']['position'];
                }

                $formData[$driver->name] = $driver->value;

                $vueDataX = $driver->getVueData();
                if ($vueDataX) {
                    $vueData = \Be\Util\Arr::merge($vueData, $vueDataX);
                }

                $vueMethodsX = $driver->getVueMethods();
                if ($vueMethodsX) {
                    $vueMethods = array_merge($vueMethods, $vueMethodsX);
                }
            }

            if ($tabHtml && $tabPosition == 'BeforeSearch') {
                echo $tabHtml;
            }

            if (isset($this->setting['lists']['search']['items']) && count($this->setting['lists']['search']['items']) > 0) {
                ?>
                <el-row>
                    <el-col :span="24">
                        <?php
                        foreach ($this->setting['lists']['search']['items'] as $item) {
                            $driver = null;
                            if (isset($item['driver'])) {
                                $driverName = $item['driver'];
                                $driver = new $driverName($item);
                            } else {
                                $driver = new \Be\Plugin\Curd\SearchItem\SearchItemInput($item);
                            }
                            echo $driver->getHtml();

                            $formData[$driver->name] = $driver->value;

                            $vueDataX = $driver->getVueData();
                            if ($vueDataX) {
                                $vueData = \Be\Util\Arr::merge($vueData, $vueDataX);
                            }

                            $vueMethodsX = $driver->getVueMethods();
                            if ($vueMethodsX) {
                                $vueMethods = array_merge($vueMethods, $vueMethodsX);
                            }
                        }
                        ?>
                        <el-form-item>
                            <el-button type="success" icon="el-icon-search" @click="search" v-loading="loading">查询</el-button>
                        </el-form-item>
                    </el-col>
                </el-row>
                <?php
            }

            if (isset($this->setting['lists']['toolbar']['items']) && count($this->setting['lists']['toolbar']['items']) > 0) {
                echo '<el-row><el-col :span="24">';
                foreach ($this->setting['lists']['toolbar']['items'] as $item) {
                    $driver = null;
                    if (isset($item['driver'])) {
                        $driverName = $item['driver'];
                        $driver = new $driverName($item);
                    } else {
                        $driver = new \Be\Plugin\Curd\ToolbarItem\ToolbarItemButton($item);
                    }
                    $toolbarItemDriverNames[] = $driver->name;

                    echo '<el-form-item>';
                    echo $driver->getHtml() . "\r\n";
                    echo '</el-form-item>';

                    $vueDataX = $driver->getVueData();
                    if ($vueDataX) {
                        $vueData = \Be\Util\Arr::merge($vueData, $vueDataX);
                    }

                    $vueMethodsX = $driver->getVueMethods();
                    if ($vueMethodsX) {
                        $vueMethods = array_merge($vueMethods, $vueMethodsX);
                    }
                }
                echo '</el-col></el-row>';
            }

            if ($tabHtml && $tabPosition == 'BeforeStage') {
                echo $tabHtml;
            }
            ?>

            <el-table
                    :data="rows"
                    ref="stageTable"
                    v-loading="loading"
                    size="mini"
                    :height="stageHeight"
                    :default-sort="{prop:orderBy,order:orderByDir}"
                    @sort-change="sort"
                    @selection-change="selectionChange">
                <?php
                $opPosition = 'right';
                if (isset($this->setting['lists']['operation'])) {

                    $operationDriver = new \Be\Plugin\Curd\Operation($this->setting['lists']['operation']);
                    $opHtml = $operationDriver->getHtmlBefore();

                    if (isset($this->setting['lists']['operation']['items'])) {
                        foreach ($this->setting['lists']['operation']['items'] as $item) {
                            $driver = null;
                            if (isset($item['driver'])) {
                                $driverName = $item['driver'];
                                $driver = new $driverName($item);
                            } else {
                                $driver = new \Be\Plugin\Curd\OperationItem\OperationItemButton($item);
                            }
                            $opHtml .= $driver->getHtml() . "\r\n";

                            $vueDataX = $driver->getVueData();
                            if ($vueDataX) {
                                $vueData = \Be\Util\Arr::merge($vueData, $vueDataX);
                            }

                            $vueMethodsX = $driver->getVueMethods();
                            if ($vueMethodsX) {
                                $vueMethods = array_merge($vueMethods, $vueMethodsX);
                            }
                        }
                    }

                    $opHtml .= $operationDriver->getHtmlAfter();

                    $vueDataX = $operationDriver->getVueData();
                    if ($vueDataX) {
                        $vueData = \Be\Util\Arr::merge($vueData, $vueDataX);
                    }

                    $vueMethodsX = $operationDriver->getVueMethods();
                    if ($vueMethodsX) {
                        $vueMethods = array_merge($vueMethods, $vueMethodsX);
                    }

                    $opPosition = $operationDriver->position;

                    if ($opPosition == 'left') {
                        echo $opHtml;
                    }
                }

                foreach ($this->setting['lists']['field']['items'] as $item) {
                    $driver = null;
                    if (isset($item['driver'])) {
                        $driverName = $item['driver'];
                        $driver = new $driverName($item);
                    } else {
                        $driver = new \Be\Plugin\Curd\FieldItem\FieldItemText($item);
                    }
                    echo $driver->getHtml();

                    $vueDataX = $driver->getVueData();
                    if ($vueDataX) {
                        $vueData = \Be\Util\Arr::merge($vueData, $vueDataX);
                    }

                    $vueMethodsX = $driver->getVueMethods();
                    if ($vueMethodsX) {
                        $vueMethods = array_merge($vueMethods, $vueMethodsX);
                    }
                }

                if (isset($this->setting['lists']['operation']) && $opPosition == 'right') {
                    echo $opHtml;
                }
                ?>
            </el-table>

            <div style="text-align: center; padding: 10px 10px 0 10px;" v-if="total > 0">
                <el-pagination
                        @size-change="changePageSize"
                        @current-change="gotoPage"
                        :current-page="page"
                        :page-sizes="[10, 15, 20, 25, 30, 50, 100, 200, 500]"
                        :page-size="pageSize"
                        layout="total, sizes, prev, pager, next, jumper"
                        :total="total">
                </el-pagination>
            </div>

        </el-form>

        <el-dialog
                :title="dialog.title"
                :visible.sync="dialog.visible"
                :width="dialog.width"
                :close-on-click-modal="false"
                :destroy-on-close="true">
            <iframe id="frame-dialog" name="frame-dialog" src="about:blank"
                    :style="{width:'100%',height:dialog.height,border:0}"></iframe>
        </el-dialog>

        <el-drawer
                :visible.sync="drawer.visible"
                :size="drawer.width"
                :title="drawer.title"
                :wrapper-closable="false"
                :destroy-on-close="true">
            <iframe id="frame-drawer" name="frame-drawer" src="about:blank"
                    style="width:100%;height:100%;border:0;"></iframe>
        </el-drawer>

    </div>

    <script>
        var pageSizeKey = "<?php echo $this->url; ?>:pageSize";
        var pageSize = localStorage.getItem(pageSizeKey);
        if (pageSize == undefined || isNaN(pageSize)) {
            pageSize = <?php echo $this->pageSize; ?>;
        } else {
            pageSize = Number(pageSize);
        }

        var vueCurdLists = new Vue({
            el: '#app',
            data: {
                formData: <?php echo json_encode($formData); ?>,
                orderBy: "",
                orderByDir: "",
                pageSize: pageSize,
                page: 1,
                pages: 1,
                total: 0,
                rows: [],
                selectedRows: [],
                loading: false,
                stageHeight: 500,
                dialog: {visible: false, width: "600px", height: "400px", title: ""},
                drawer: {visible: false, width: "40%", title: ""}<?php
                if ($vueData) {
                    foreach ($vueData as $k => $v) {
                        echo ',' . $k . ':' . json_encode($v);
                    }
                }
                ?>
            },
            created: function () {
                this.search();
                <?php
                if (isset($this->setting['lists']['reload']) && is_numeric($this->setting['lists']['reload'])) {
                    echo 'var _this = this;';
                    echo 'setInterval(function () {_this.reload();}, ' . $this->setting['lists']['reload'] . ');';
                }
                ?>
            },
            methods: {
                search: function () {
                    this.page = 1;
                    this.loadData();
                },
                loadData: function () {
                    this.loading = true;
                    var _this = this;
                    _this.$http.post("<?php echo $this->url; ?>", {
                        formData: _this.formData,
                        orderBy: _this.orderBy,
                        orderByDir: _this.orderByDir,
                        page: _this.page,
                        pageSize: _this.pageSize
                    }).then(function (response) {
                        _this.loading = false;
                        //console.log(response);
                        if (response.status == 200) {
                            var responseData = response.data;
                            if (responseData.success) {
                                _this.total = parseInt(responseData.data.total);
                                _this.rows = responseData.data.rows;
                                _this.pages = Math.floor(_this.total / _this.pageSize);
                            } else {
                                _this.total = 0;
                                _this.rows = [];
                                _this.page = 1;
                                _this.pages = 1;

                                if (responseData.message) {
                                    _this.$message.error(responseData.message);
                                }
                            }
                            _this.updateToolbars();
                        }
                    }).catch(function (error) {
                        _this.loading = false;
                        _this.$message.error(error);
                    });
                },
                reload: function () {
                    var _this = this;
                    _this.$http.post("<?php echo $this->url; ?>", {
                        formData: _this.formData,
                        orderBy: _this.orderBy,
                        orderByDir: _this.orderByDir,
                        page: _this.page,
                        pageSize: _this.pageSize
                    }).then(function (response) {
                        if (response.status == 200) {
                            var responseData = response.data;
                            if (responseData.success) {
                                _this.total = parseInt(responseData.data.total);
                                _this.rows = responseData.data.rows;
                                _this.pages = Math.floor(_this.total / _this.pageSize);
                            }
                            _this.updateToolbars();
                        }
                    });
                },
                changePageSize: function (pageSize) {
                    this.pageSize = pageSize;
                    this.page = 1;
                    localStorage.setItem(pageSizeKey, pageSize);
                    this.loadData();
                },
                gotoPage: function (page) {
                    this.page = page;
                    this.loadData();
                },
                sort: function (option) {
                    if (option.order == "ascending" || option.order == "descending") {
                        this.orderBy = option.prop;
                        this.orderByDir = option.order == "ascending" ? "ASC" : "DESC";
                    } else {
                        this.orderBy = "";
                        this.orderByDir = "";
                    }
                    this.loadData();
                },
                toolbarAction: function (name, option) {
                    var data = {
                        formData: this.formData,
                        orderBy: this.orderBy,
                        orderByDir: this.orderByDir,
                        page: this.page,
                        pageSize: this.pageSize
                    };

                    data.postData = option.postData;
                    data.selectedRows = this.selectedRows;
                    return this.action(option, data);
                },
                fieldAction: function (name, option, row) {
                    switch (option.target) {
                        case "dialog":
                            option.dialog.title = row[name];
                            break;
                        case "drawer":
                            option.drawer.title = row[name];
                            break;
                    }

                    var data = {};
                    data.postData = option.postData;
                    data.row = row;
                    return this.action(option, data);
                },
                operationAction: function (name, option, row) {
                    var data = {};
                    data.postData = option.postData;
                    data.row = row;
                    return this.action(option, data);
                },
                action: function (option, data) {
                    if (option.target == 'ajax') {
                        var _this = this;
                        this.$http.post(option.url, data).then(function (response) {
                            if (response.status == 200) {
                                if (response.data.success) {
                                    _this.$message.success(response.data.message);
                                } else {
                                    if (response.data.message) {
                                        _this.$message.error(response.data.message);
                                    }
                                }
                                _this.loadData();
                            }
                        }).catch(function (error) {
                            _this.$message.error(error);
                            _this.loadData();
                        });
                    } else {
                        var eForm = document.createElement("form");
                        eForm.action = option.url;
                        switch (option.target) {
                            case "self":
                            case "_self":
                                eForm.target = "_self";
                                break;
                            case "blank":
                            case "_blank":
                                eForm.target = "_blank";
                                break;
                            case "dialog":
                                eForm.target = "frame-dialog";
                                this.dialog.title = option.dialog.title;
                                this.dialog.visible = true;
                                break;
                            case "drawer":
                                eForm.target = "frame-drawer";
                                this.drawer.title = option.drawer.title;
                                this.drawer.visible = true;
                                break;
                        }
                        eForm.method = "post";
                        eForm.style.display = "none";

                        var e = document.createElement("textarea");
                        e.name = 'data';
                        e.value = JSON.stringify(data);
                        eForm.appendChild(e);

                        document.body.appendChild(eForm);

                        setTimeout(function () {
                            eForm.submit();
                        }, 50);

                        setTimeout(function () {
                            document.body.removeChild(eForm);
                        }, 3000);
                    }

                    return false;
                },
                hideDialog: function () {
                    this.dialog.visible = false;
                },
                hideDrawer: function () {
                    this.drawer.visible = false;
                },
                selectionChange: function(rows) {
                    this.selectedRows = rows;
                    this.updateToolbars();
                },
                updateToolbars: function() {
                    var toolbarEnable;
                    <?php
                    if (isset($this->setting['lists']['toolbar']['items']) && count($this->setting['lists']['toolbar']['items']) > 0) {
                        $i = 0;
                        foreach ($this->setting['lists']['toolbar']['items'] as $item) {
                            if (isset($item['task']) &&
                                $item['task'] == 'fieldEdit' &&
                                isset($item['postData']['field']) &&
                                isset($item['postData']['value'])) {
                                ?>
                                if (this.selectedRows.length > 0) {
                                    toolbarEnable = true;
                                    for(var x in this.selectedRows) {
                                        if (this.selectedRows[x].<?php echo $item['postData']['field']; ?> == "<?php echo $item['postData']['value']; ?>") {
                                            toolbarEnable = false;
                                        }
                                    }
                                } else {
                                    toolbarEnable = false;
                                }
                                this.toolbar.<?php echo $toolbarItemDriverNames[$i]; ?>.enable = toolbarEnable;
                                <?php
                            }
                            $i++;
                        }
                    }
                    ?>
                }
                <?php
                if ($vueMethods) {
                    foreach ($vueMethods as $k => $v) {
                        echo ',' . $k . ':' . $v;
                    }
                }
                ?>
            },
            mounted: function () {
                this.$nextTick(function () {
                    this.stageHeight = document.documentElement.clientHeight - this.$refs.stageTable.$el.offsetTop - 50;
                    var self = this;
                    window.onresize = function () {
                        self.stageHeight = document.documentElement.clientHeight - self.$refs.stageTable.$el.offsetTop - 50
                    }
                })
            }

        });
    </script>
</be-center>
