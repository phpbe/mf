<be-center>
<?php
$primaryKey = $this->table->getPrimaryKey();
$runtime = \Be\System\Be::getRuntime();
?>
<div id="app" v-cloak>

    <div class="importer-preview">

        <el-form :form="be_form" layout="inline" @submit="handleSubmit">
            <div class="importer-preview-data">




            </div>
        </el-form>
    </div>
</div>


<script>
    var app = new Vue({
        el: '#app',
        data: function() {
            return {
                be_saving: false,
                be_form: this.$form.createForm(this)
            };
        },
        methods: {
            handleSubmit: function (e) {
                e.preventDefault();

                var _this = this;
                this.be_form.validateFields(function(err, values){
                    if (!err) {
                        _this.be_saving = true;
                        _this.$http.post("<?php echo beUrl($runtime->getAppName() . '.' . $runtime->getControllerName() . '.' . $runtime->getActionName(), ['appName' => $this->appName]); ?>", values)
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
