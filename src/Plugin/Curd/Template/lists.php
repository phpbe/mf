<be-center>
    <?php
    $primaryKey = $this->table->getPrimaryKey();

    $searchForm = [];
    ?>
    <div id="app" v-cloak>

        <el-form :inline="true" size="small">

            <?php
            $tabHtml = '';
            $tabPosition = 'BeforeSearch';
            if (isset($this->setting['lists']['tab']['name']) && isset($this->setting['lists']['tab']['keyValues']) && count($this->setting['lists']['tab']['keyValues']) > 0) {
                $driver = new \Be\Plugin\Lists\Tab();
                $tabHtml = $driver->getHtml();
                if (isset($this->setting['lists']['tab']['position'])) {
                    $tabPosition = $this->setting['lists']['tab']['position'];
                }
            }

            if ($tabHtml && $tabPosition == 'BeforeSearch') {
                echo $tabHtml;
            }

            if (isset($this->setting['lists']['search']['items']) && count($this->setting['lists']['search']['items']) > 0) {
                foreach ($this->setting['lists']['search']['items'] as $item) {
                    $driver = isset($item['driver']) ? $item['driver'] : '\\Be\\Plugin\\Lists\\SearchItem\\SearchItemInput';
                    $driver = new $driver($item);
                    echo $driver->getHtml();
                    $searchForm[$driver->name] = $driver->value;
                }
            }
            ?>

            <el-form-item>
                <el-button type="success" icon="el-icon-search" @click="search">查询</el-button>
            </el-form-item>

            <?php
            if (isset($this->setting['lists']['toolbar']['items']) && count($this->setting['lists']['toolbar']['items']) > 0) {
                foreach ($this->setting['lists']['toolbar']['items'] as $item) {
                    $driver = isset($item['driver']) ? $item['driver'] : '\\Be\\Plugin\\Lists\\ToolbarItem\\ToolbarItemButton';
                    $driver = new $driver($item);
                    echo '<el-form-item>';
                    echo $driver->getHtml();
                    echo '</el-form-item>';
                }
            }

            if ($tabHtml && $tabPosition == 'BeforeStage') {
                echo $tabHtml;
            }
            ?>

            <el-table
                    :data="tuples"
                    ref="stageTable"
                    v-loading="loading"
                    size="mini"
                    :height="stageHeight">
                <?php
                $opPosition = 'right';
                if (isset($this->setting['lists']['operation']['position']) && in_array($this->setting['lists']['operation']['position'], ['left', 'right'])) {
                    $opPosition = $this->setting['lists']['operation']['position'];
                }

                $opHtml = '';
                if (isset($this->setting['lists']['operation']['items'])) {
                    foreach ($this->setting['lists']['operation']['items'] as $item) {
                        $driver = isset($item['driver']) ? $item['driver'] : '\\Be\\Plugin\\Lists\\OperationItem\\OperationItemButton';
                        $driver = new $driver($item);
                        $opHtml .= $driver->getHtml();
                    }
                }

                if ($opPosition == 'left') {
                    echo $opHtml;
                }

                foreach ($this->setting['lists']['fields']['items'] as $item) {
                    $driver = isset($item['driver']) ? $item['driver'] : '\\Be\\Plugin\\Lists\\FieldItem\\FieldItemText';
                    $driver = new $driver($item);
                    echo $driver->getHtml();
                }

                if ($opPosition == 'right') {
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

    </div>

    <script>

        var pageSizeKey = "<?php echo $this->url; ?>:pageSize";
        var pageSize = localStorage.getItem(pageSizeKey);
        if (pageSize == undefined || isNaN(pageSize)) {
            pageSize = <?php echo $this->pageSize; ?>;
        } else {
            pageSize = Number(pageSize);
        }

        var app = new Vue({
            el: '#app',
            data: {
                searchForm: <?php echo json_encode($searchForm); ?>,
                pageSize: pageSize,
                page: 1,
                pages: 1,
                total: 0,
                tuples: [],
                loading: false,
                stageHeight: 500
            },
            created: function () {
                this.search();
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
                        page: _this.page,
                        pageSize: _this.pageSize
                    }).then(function (response) {
                        _this.loading = false;
                        //console.log(response);
                        if (response.status == 200) {
                            var responseData = response.data;
                            if (responseData.success) {
                                _this.total = parseInt(responseData.data.total);
                                _this.tuples = responseData.data.tuples;
                                _this.pages = Math.floor(_this.total / _this.pageSize);
                            } else {
                                _this.total = 0;
                                _this.tuples = [];
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
                changePageSize: function (pageSize) {
                    this.pageSize = pageSize;
                    this.page = 1;
                    localStorage.setItem(pageSizeKey, pageSize);
                    this.loadData();
                },
                gotoPage: function (page) {
                    this.page = page;
                    this.loadData();
                }
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

        //console.log(app);
    </script>
</be-center>
