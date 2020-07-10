<be-center>
    <?php
    $primaryKey = $this->table->getPrimaryKey();

    $searchForm = [];
    $vueData = [];
    $vueMethods = [];
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

                $searchForm[$driver->name] = $driver->value;

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
                foreach ($this->setting['lists']['search']['items'] as $item) {
                    $driver = null;
                    if (isset($item['driver'])) {
                        $driverName = $item['driver'];
                        $driver = new $driverName($item);
                    } else {
                        $driver = new \Be\Plugin\Curd\SearchItem\SearchItemInput($item);
                    }
                    echo $driver->getHtml();

                    $searchForm[$driver->name] = $driver->value;

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
            ?>

            <el-form-item>
                <el-button type="success" icon="el-icon-search" @click="search">查询</el-button>
            </el-form-item>

            <?php
            if (isset($this->setting['lists']['toolbar']['items']) && count($this->setting['lists']['toolbar']['items']) > 0) {
                foreach ($this->setting['lists']['toolbar']['items'] as $item) {
                    $driver = null;
                    if (isset($item['driver'])) {
                        $driverName = $item['driver'];
                        $driver = new $driverName($item);
                    } else {
                        $driver = new \Be\Plugin\Curd\ToolbarItem\ToolbarItemButton($item);
                    }
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
                    @sort-change="sort">
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
                searchForm: <?php echo json_encode($searchForm); ?>,
                orderBy: "",
                orderByDir: "",
                pageSize: pageSize,
                page: 1,
                pages: 1,
                total: 0,
                rows: [],
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
                        searchForm: _this.searchForm,
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
                        }
                    }).catch(function (error) {
                        _this.loading = false;
                        _this.$message.error(error);
                    });
                },
                reload: function () {
                    var _this = this;
                    _this.$http.post("<?php echo $this->url; ?>", {
                        searchForm: _this.searchForm,
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
                toolbarAction: function (option) {
                    var data = {
                        searchForm: this.searchForm,
                        orderBy: this.orderBy,
                        orderByDir: this.orderByDir,
                        page: this.page,
                        pageSize: this.pageSize
                    };

                    if (option.postData.length > 0) {
                        data.postData = option.postData;
                    }

                    return this.action(option, data);
                },
                fieldAction: function (option, row) {
                    var data;
                    if (option.postData.length > 0) {
                        data = option.postData;
                    } else {
                        data = {};
                    }

                    <?php
                    if (is_array($primaryKey)) {
                        foreach ($primaryKey as $pKey) {
                            echo 'data["' . $pKey . '""]=row.' . $pKey . ';';
                        }
                    } else {
                        echo 'data["' . $primaryKey . '"]=row.' . $primaryKey . ';';
                    }
                    ?>

                   return this.action(option, data);
                },
                operationAction: function (option, row) {
                    return this.fieldAction(option, row);
                },
                action: function (option, data) {
                    if (option.target == 'ajax') {
                        var tmpLoading = this.$loading({
                            lock: true,
                            text: '处理中...',
                            spinner: 'el-icon-loading',
                            background: 'rgba(0, 0, 0, 0.3)'
                        });

                        var _this = this;
                        this.$http.post(option.url, data).then(function (response) {
                            loading.close();
                            if (response.status == 200) {
                                var responseData = response.data;
                                if (responseData.success) {
                                    _this.loadData();
                                } else {
                                    if (responseData.message) {
                                        _this.$message.error(responseData.message);
                                    }
                                }
                            }
                        }).catch(function (error) {
                            tmpLoading.close();
                            _this.$message.error(error);
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
