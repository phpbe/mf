<be-head>
    <script>


    </script>
</be-head>


<be-center>
    <?php
    $primaryKey = $this->table->getPrimaryKey();
    ?>
    <div id="app" v-cloak>

        <el-form :form="be_form" layout="inline" @submit="handleSubmit">

            <?php
            if (isset($this->setting['lists']['search']['items']) && count($this->setting['lists']['search']['items']) > 0) {
                foreach ($this->setting['lists']['search']['items'] as $item) {
                    $driver = $item['driver'];
                    $driver = new $driver($item);
                    echo $driver->getHtml();
                }
            }
            ?>

            <?php
            if (isset($this->setting['lists']['toolbar']['items']) && count($this->setting['lists']['toolbar']['items']) > 0) {
                foreach ($this->setting['lists']['toolbar']['items'] as $item) {
                    $driver = $item['driver'];
                    $driver = new $driver($item);
                    echo $driver->getHtml();
                }
            }
            ?>

            <div class="curd-lists-data">


            </div>

        </el-form>

    </div>

    <script>
        var app = new Vue({
            el: '#app',
            data: function () {
                return {
                    be_saving: false,
                    be_form: this.$form.createForm(this)
                };
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
