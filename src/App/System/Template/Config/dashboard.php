<be-center>
    <?php
    $formData = [];
    $vueData = [];
    $vueMethods = [];
    ?>
    <div id="app" v-cloak>

        <el-tabs tab-position="left" value="<?php echo $this->appName; ?>">
            <?php foreach ($this->configTree as $x) { ?>
                <el-tab-pane name="<?php echo $x['app']->name; ?>">
                    <span slot="label"><i class="<?php echo $x['app']->icon; ?>"></i> <?php echo $x['app']->label; ?></span>

                    <el-tabs tab-position="left" value="<?php echo $this->appName . '-' . $this->configName; ?>" @tab-click="goto">
                        <?php
                        foreach ($x['configs'] as $xx) {
                            ?>
                            <el-tab-pane name="<?php echo $xx['appName'] . '-' . $xx['configName']; ?>" label="<?php echo $xx['annotation']->value; ?>">
                                <?php
                                if ($xx['appName'] == $this->appName && $xx['configName'] == $this->configName) {
                                    if (count($this->config['items'])) {
                                        ?>
                                        <div style="max-width: 800px;">
                                            <el-form size="small" label-width="200px" :disabled="loading">
                                                <?php
                                                foreach ($this->config['items'] as $key => $configItem) {
                                                    $driver = $configItem['driver'];
                                                    echo $driver->getHtml();

                                                    $formData[$driver->name] = $driver->getValueString();

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
                                                    <el-button type="success" icon="el-icon-check" @click="saveConfig">保存</el-button>
                                                    <el-button type="danger" icon="el-icon-close" @click="resetConfig">恢复默认值</el-button>
                                                    <?php if ($this->config['annotation']->test) { ?>
                                                        <el-button icon="el-icon-view" @click="window.open('<?php echo $this->config['annotation']->test; ?>');">测试</el-button>
                                                    <?php } ?>
                                                </el-form-item>
                                            </el-form>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>
                            </el-tab-pane>
                            <?php
                        }
                        ?>
                    </el-tabs>
                </el-tab-pane>
            <?php } ?>
        </el-tabs>
    </div>

    <script>
        var app = new Vue({
            el: '#app',
            data: {
                formData: <?php echo json_encode($formData); ?>,
                loading: false<?php
                if ($vueData) {
                    foreach ($vueData as $k => $v) {
                        echo ',' . $k . ':' . json_encode($v);
                    }
                }
                ?>
            },
            methods: {
                saveConfig: function () {
                    this.loading = true;
                    var _this = this;
                    _this.$http.post("<?php echo beUrl('System.Config.saveConfig', ['appName' => $this->appName, 'configName' => $this->configName]); ?>",_this.formData)
                        .then(function (response) {
                            _this.loading = false;
                            if (response.status == 200) {
                                if (response.data.success) {
                                    _this.$message.success(response.data.message);
                                } else {
                                    _this.$message.error(response.data.message);
                                }
                            }
                        }).catch(function (error) {
                            _this.loading = false;
                            _this.$message.error(error);
                        });
                },
                resetConfig: function () {
                    var _this = this;
                    this.$confirm('该操作不可恢复，确认恢复默认值吗？', '确认恢复默认值吗', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }).then(function () {
                        _this.loading = true;
                        _this.$http.get("<?php echo beUrl('System.Config.resetConfig', ['appName' => $this->appName, 'configName' => $this->configName]); ?>")
                            .then(function (response) {
                                _this.loading = false;
                                if (response.status == 200) {
                                    if (response.data.success) {
                                        _this.$message.success(response.data.message);
                                        window.location.reload();
                                    } else {
                                        _this.$message.error(response.data.message);
                                    }
                                }
                            })
                            .catch(function (error) {
                                _this.loading = false;
                                _this.$message.error(error);
                            });
                    }).catch(function () {
                        _this.loading = false;
                    });
                },
                goto: function (tab) {
                    var arr = tab.name.split('-');
                    window.location.href = '<?php echo beUrl('System.Config.dashboard'); ?>?appName=' + arr[0] + '&configName=' + arr[1];
                }
                <?php
                if ($vueMethods) {
                    foreach ($vueMethods as $k => $v) {
                        echo ',' . $k . ':' . $v;
                    }
                }
                ?>
            }
        });
    </script>
</be-center>
