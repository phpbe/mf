<be-center>
    <?php
    $primaryKey = $this->table->getPrimaryKey();
    ?>
    <div id="app" v-cloak>

        <el-form layout="inline" @submit="handleSubmit">

            <?php
            $tabHtml = '';
            $tabPosition = 'BeforeSearch';
            if (isset($this->setting['lists']['tab']['field']) && isset($this->setting['lists']['tab']['keyValues']) && count($this->setting['lists']['tab']['keyValues']) > 0) {
                $tabHtml .= '<el-tabs v-model="searchForm.'.$this->setting['lists']['tab']['field'].'" @tab-click="tabClick">';
                foreach ($this->setting['lists']['tab']['keyValues'] as $key => $val) {
                    $tabHtml .= '<el-tab-pane label="'.$val.'" name="'.$key.'"></el-tab-pane>';
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
                    $driver = $item['driver'];
                    $driver = new $driver($item);
                    echo $driver->getHtml();
                }
            }
            ?>

            <el-form-item>
                <el-button type="success" icon="el-icon-search" size="small" @click="search">查询</el-button>
            </el-form-item>

            <?php
            if (isset($this->setting['lists']['toolbar']['items']) && count($this->setting['lists']['toolbar']['items']) > 0) {
                foreach ($this->setting['lists']['toolbar']['items'] as $item) {
                    $driver = $item['driver'];
                    $driver = new $driver($item);
                    echo $driver->getHtml();
                }
            }
            ?>


            <?php
            $searchForm = [];

            if (isset($config['action']['index']['tabs']) && count($config['action']['index']['tabs']) > 0)
            {
                $tabs = $config['action']['index']['tabs'];
                $searchForm[$tabs['name']] = $tabs['value'];

                echo '<el-tabs v-model="searchForm.'.$tabs['name'].'" @tab-click="tabClick">';
                foreach($tabs['keyValues'] as $key => $val) {
                    echo '<el-tab-pane label="'.$val.'" name="'.$key.'"></el-tab-pane>';
                }
                echo '</el-tabs>';
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
                <?php echo $subtotal ? 'show-summary :summary-method="getSummary"' : ''; ?>
                    :height="tableHeight"
                    @selection-change="handleSelectionChange"
            >
                <?php
                $opPosition = 'right';


                if (isset($config['action']['index']['op']['position']) && in_array($config['action']['index']['op']['position'], ['left', 'right'])) {
                    $opPosition = $config['action']['index']['op']['position'];
                }

                $opHtml = '';
                if (isset($config['action']['index']['op'])) {

                    $opConfig = $config['action']['index']['op'];
                    $opHtml .= '<el-table-column';
                    $opHtml .= ' label="'.$opConfig['name'].'"';

                    if (isset($field['fixed'])) {
                        echo ' fixed="'.$field['fixed'].'"';
                    }

                    if (isset($opConfig['width'])) {
                        $opHtml .= ' width="'.$opConfig['width'].'"';
                    }
                    if (isset($opConfig['align'])) {
                        $opHtml .= ' align="'.$opConfig['align'].'"';
                    }

                    if (isset($opConfig['header-align'])) {
                        $opHtml .= ' header-align="'.$opConfig['header-align'].'"';
                    } else {
                        if (isset($opConfig['align'])) {
                            $opHtml .= ' header-align="'.$opConfig['align'].'"';
                        }
                    }
                    $opHtml .= '>';

                    $opHtml .= '<template slot-scope="scope">';
                    $opHtml .= '<div v-html="scope.row._op"></div>';
                    $opHtml .= '</template>';

                    $opHtml .=  '</el-table-column>';
                }

                if ($opPosition == 'left') {
                    echo $opHtml;
                }

                foreach($config['action']['index']['fields'] as $key => $field)
                {
                    echo '<el-table-column';
                    if (isset($field['type']) && $field['type'] == 'selection') {
                        echo ' type="selection"';
                    } else {
                        echo ' prop="'.$key.'"';
                        echo ' label="'.$field['name'].'"';
                    }

                    if (isset($field['fixed'])) {
                        echo ' fixed="'.$field['fixed'].'"';
                    }

                    if (isset($field['width'])) {
                        echo ' width="'.$field['width'].'"';
                    }
                    if (isset($field['align'])) {
                        echo ' align="'.$field['align'].'"';
                    } else {
                        echo ' align="center"';
                    }

                    if (isset($field['header-align'])) {
                        echo ' header-align="'.$field['header-align'].'"';
                    } else {
                        if (isset($field['align'])) {
                            echo ' header-align="'.$field['align'].'"';
                        } else {
                            echo ' header-align="center"';
                        }
                    }
                    echo '>';

                    if (isset($field['value']) || isset($field['template'])) {
                        echo '<template slot-scope="scope">';
                        echo '<div v-html="scope.row.'.$key.'"></div>';
                        echo '</template>';
                    }
                    echo '</el-table-column>';
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
                searchForm : <?php echo json_encode($searchForm); ?>,
                pageSize : <?php echo $config['limit']; ?>,
                page : 1,
                pages : 1,
                total : 0,
                rows : [],
                subtotal : [],
                loading : false,
                tableHeight: 500,
                multipleSelection: []
            },
            methods: {

                handleSubmit: function (e) {
                    e.preventDefault();

                    var _this = this;
                    this.be_form.validateFields(function (err, values) {
                        if (!err) {
                            _this.be_saving = true;
                            _this.$http.post("<?php echo beUrl('System.Config.saveConfig'); ?>", values)
                                .then(function (response) {
                                    _this.be_saving = false;
                                    if (response.status == 200) {
                                        if (response.data.success) {
                                            _this.$message.success(response.data.message);
                                        } else {
                                            _this.$message.error(response.data.message);
                                        }
                                    }
                                })
                                .catch(function (error) {
                                    _this.be_saving = false;
                                    _this.$message.error(error);
                                });
                        }
                    });
                }
            },

            mounted: function () {
            }

        });

        //console.log(app);
    </script>
</be-center>
