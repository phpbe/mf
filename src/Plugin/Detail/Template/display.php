<be-center>
    <?php
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
                        $driver = new \Be\Plugin\Detail\Item\DetailItemText($item);
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
            }
            ?>
            <el-form-item>
                <el-button @click="close">关闭</el-button>
            </el-form-item>
        </el-form>
    </div>

    <script>
        var vueDetail = new Vue({
            el: '#app',
            data: {<?php
                if ($vueData) {
                    foreach ($vueData as $k => $v) {
                        echo ',' . $k . ':' . json_encode($v);
                    }
                }
                ?>
            },
            methods: {
                close: function () {
                    if(self.frameElement != null && (self.frameElement.tagName == "IFRAME" || self.frameElement.tagName == "iframe")){
                        parent.close();
                    } else {
                        window.close();
                    }
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
