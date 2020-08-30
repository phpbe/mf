<be-center>
    <?php
    $vueData = [];
    $vueMethods = [];
    ?>
    <div id="app">
        <el-form<?php
            foreach ($this->setting['field']['ui']['form'] as $k => $v) {
                if ($v === null) {
                    echo ' '.$k;
                } else {
                    echo ' '.$k.'="' . $v . '"';
                }
            }
            echo '>';

            if (isset($this->setting['field']['items']) && count($this->setting['field']['items']) > 0) {
                foreach ($this->setting['field']['items'] as $item) {
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