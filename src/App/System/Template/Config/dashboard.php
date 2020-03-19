<?php
use Be\System\Be;
?>

<!--{head}-->
<script src="<?php echo Be::getProperty('App.System')->path; ?>/Tempalte/Config/js/base64.min.js"></script>
<!--{/head}-->

<!--{center}-->

<?php
$formData = [];


$data = [];
$methods = [];
?>
<div id="app" v-cloak>
    <a-card>
        <a-tabs default-active-key="<?php echo $this->appName; ?>"  size="small" tab-position="left">
            <?php
            //print_r($this->configTree);
            foreach ($this->configTree as $x) {
                echo '<a-tab-pane key="'.$x['app']->name.'">';
                echo '<span slot="tab">';
                echo '<a-icon type="'.$x['app']->icon.'"></a-icon>';
                echo $x['app']->label;
                echo '</span>';
                echo '<a-tabs default-active-key="'. $this->appName.'-'.$this->configName.'" size="small" tab-position="left" @change="goto">';
                foreach ($x['configs'] as $xx) {
                    echo '<a-tab-pane key="'. $x['app']->name.'-'.$xx['name'].'" tab="'.$xx['label'].'">';
                    if ($x['app']->name == $this->appName && $xx['name'] == $this->configName) {
                        if (count($this->config['items'])) {
                            echo '<a-form :form="be_form" layout="horizontal" style="max-width:800px" @submit="handleSubmit">';
                            foreach ($this->config['items'] as $key => $configItem) {
                                $formData[$key] = $configItem->value;
                                echo $configItem->getEditHtml();

                                $tmpData = $configItem->getEditData();
                                if ($tmpData) {
                                    $data[$configItem->name] = $tmpData;
                                }

                                $tmpMethod = $configItem->getEditMethods();
                                if ($tmpMethod) {
                                    $methods = array_merge($methods, $tmpMethod);
                                }
                            }
                            echo ' <a-form-item :wrapper-col="{span:18,offset:6}">';
                            echo '<a-button type="primary" icon="save" html-type="submit" :loading="be_saving">保存</a-button>';
                            echo '<a-button type="danger" icon="undo" :style="{marginLeft: \'8px\'}" @click="resetConfig">恢复默认值</a-button>';
                            if (isset($this->config['test'])) {
                                echo '<a-button icon="question" :style="{marginLeft: \'8px\'}" onclick="window.open(\'' . $this->config['test'] . '\');">测试</a-button>';
                            }
                            echo '</a-form-item>';
                            echo ' </a-form>';
                        }
                    }
                    echo '</a-tab-pane>';
                }
                echo '</a-tabs>';
                echo '</a-tab-pane>';
            }
            ?>
        </a-tabs>
    </a-card>
</div>

<script>
    var app = new Vue({
        el: '#app',
        data: function() {
            return {
                be_saving: false,
                be_form: this.$form.createForm(this)<?php
                if ($data) {
                    foreach ($data as $key => $v) {
                        echo ',' . $key . ': ' . json_encode($v);
                    }
                }
                ?>
            };
        },
        methods: {
            handleSubmit: function (e) {
                e.preventDefault();

                var _this = this;
                this.be_form.validateFields(function(err, values){
                    if (!err) {
                        _this.be_saving = true;
                        _this.$http.post("<?php echo url('System.Config.saveConfig', ['appName' => $this->appName, 'configName' => $this->configName]); ?>", values)
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
            },
            goto: function (key) {
                var arr = key.split('-');
                window.location.href = '<?php echo url('System.Config.dashboard'); ?>?appName=' + arr[0] + '&configName=' + arr[1];
            },

            resetConfig: function () {

                var _this = this;
                this.$confirm({
                    title: '确认恢复默认值吗？',
                    content: '该操作不可恢复，确认恢复默认值吗？',
                    okText: '确认',
                    cancelText: '取消',
                    onOk: function() {

                        _this.$http.get("<?php echo url('System.Config.resetConfig', ['appName' => $this->appName, 'configName' => $this->configName]); ?>")
                            .then(function (response) {
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
                                _this.$message.error(error);
                            });

                    },
                    onCancel: function() {}
                });
            }

            <?php
            if ($methods) { echo ',', implode(',', $methods); }
            ?>
        },

        mounted: function () {
            this.be_form.setFieldsValue(<?php echo json_encode($formData); ?>);
        }

    });

    //console.log(app);
</script>
<!--{/center}-->
