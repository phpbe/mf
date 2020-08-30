<be-center>
    <?php
    $formData = [];
    $vueData = [];
    $vueMethods = [];
    ?>
    <div id="app">
        <el-form<?php
            foreach ($this->setting['form']['ui'] as $k => $v) {
                if ($v === null) {
                    echo ' '.$k;
                } else {
                    echo ' '.$k.'="' . $v . '"';
                }
            }
            echo '>';

            if (isset($this->setting['form']['items']) && count($this->setting['form']['items']) > 0) {
                foreach ($this->setting['form']['items'] as $item) {
                    $driver = null;
                    if (isset($item['driver'])) {
                        $driverName = $item['driver'];
                        $driver = new $driverName($item);
                    } else {
                        $driver = new \Be\Plugin\Form\Item\FormItemInput($item);
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
            }
            ?>
        </el-form>
    </div>

    <script>
        var vueForm = new Vue({
            el: '#app',
            data: {
                formData: <?php echo json_encode($formData); ?><?php
                if ($vueData) {
                    foreach ($vueData as $k => $v) {
                        echo ',' . $k . ':' . json_encode($v);
                    }
                }
                ?>
            },
            methods: {
                save: function () {
                    this.loading = true;
                    var _this = this;
                    _this.$http.post("<?php echo $this->setting['form']['action']; ?>", {
                        formData: _this.formData
                    }).then(function (response) {
                        _this.loading = false;
                        //console.log(response);
                        if (response.status == 200) {
                            var responseData = response.data;
                            if (responseData.success) {
                                if (responseData.message) {
                                    _this.$message.success(responseData.message);
                                }
                            } else {

                                if (responseData.message) {
                                    _this.$message.error(responseData.message);
                                }
                            }
                        }
                    }).catch(function (error) {
                        _this.loading = false;
                        _this.$message.error(error);
                    });
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
