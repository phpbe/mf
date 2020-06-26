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
            if (isset($this->setting['lists']['tab']['field']) && isset($this->setting['lists']['tab']['keyValues']) && count($this->setting['lists']['tab']['keyValues']) > 0) {
                $tabHtml .= '<el-tabs v-model="searchForm.' . $this->setting['lists']['tab']['field'] . '" @tab-click="tabClick">';
                foreach ($this->setting['lists']['tab']['keyValues'] as $key => $val) {
                    $tabHtml .= '<el-tab-pane label="' . $val . '" name="' . $key . '"></el-tab-pane>';
                }
                $tabHtml .= '</el-tabs>';
                if (isset($this->setting['lists']['tab']['position'])) {
                    $tabPosition = $this->setting['lists']['tab']['position'];
                }
            }

            if ($tabHtml && $tabPosition == 'BeforeSearch') {
                echo $tabHtml;
            }
            ?>


            <?php
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
            ?>

            <?php
            if ($tabHtml && $tabPosition == 'BeforeList') {
                echo $tabHtml;
            }
            ?>

            <el-table
                    :data="rows"
                    ref="indexTable"
                    v-loading="loading"
                    size="mini"
                    :height="tableHeight"
                    @selection-change="handleSelectionChange"
            >
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

                foreach ($this->setting['lists']['list']['items'] as $item) {
                    $driver = isset($item['driver']) ? $item['driver'] : '\\Be\\Plugin\\Lists\\ListItem\\ListItemText';
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
        var app = new Vue({
            el: '#app',
            data: {
                searchForm: <?php echo json_encode($searchForm); ?>,
                pageSize: <?php echo $this->pageSize; ?>,
                page: 1,
                pages: 1,
                total: 0,
                rows: [],
                subtotal: [],
                loading: false,
                tableHeight: 500,
                multipleSelection: []
            },
            methods: {
                search: function (e) {
                },
                changePageSize: function (pageSize) {
                    this.pageSize = pageSize;
                    this.page = 1;
                    this.loadData();
                },
                gotoPage: function (page) {
                    this.page = page;
                    this.loadData();
                },
                handleSelectionChange: function (val) {
                    this.multipleSelection = val;
                }
            },

            mounted: function () {
                this.$nextTick(function () {
                    this.tableHeight = document.documentElement.clientHeight - this.$refs.indexTable.$el.offsetTop - 50;
                    var self = this;
                    window.onresize = function () {
                        self.tableHeight = document.documentElement.clientHeight - self.$refs.indexTable.$el.offsetTop - 50
                    }
                })
            }

        });

        //console.log(app);
    </script>
</be-center>
