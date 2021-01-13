<be-head>
    <link type="text/css" rel="stylesheet"
          href="<?php echo \Be\Mf\Be::getProperty('App.System')->getUrl(); ?>/Template/System/css/exception.css">
    <link rel="stylesheet"
          href="<?php echo \Be\Mf\Be::getProperty('App.System')->getUrl(); ?>/Template/System/google-code-prettify/prettify.css"
          type="text/css"/>
    <script type="text/javascript" language="javascript"
            src="<?php echo \Be\Mf\Be::getProperty('App.System')->getUrl(); ?>/Template/System/google-code-prettify/prettify.js"></script>
    <style type="text/css">
        pre.prettyprint {
            background-color: #fff;
            color: #000;
            white-space: pre-wrap;
            word-wrap: break-word;
            border-color: #ddd;
        }
    </style>
</be-head>

<be-body>

    <div id="app" v-cloak>
        <?php
        $configSystem = \Be\Mf\Be::getConfig('System.System');
        if ($configSystem->developer) {
            ?>
            <el-alert
                    title="<?php echo $this->e->getMessage(); ?>"
                    type="error"
                    description="<?php echo '#' . $this->logHash; ?>"
                    show-icon>
            </el-alert>

            <el-tabs v-model="activeTab" type="border-card">
                <el-tab-pane label="错误跟踪信息" name="tab-trace">
                    <pre class="prettyprint linenums"><?php print_r($this->e->getTrace()); ?></pre>
                </el-tab-pane>
                <el-tab-pane label="$_SERVER" name="tab-server">
                    <pre class="prettyprint linenums"><?php print_r($_SERVER) ?></pre>
                </el-tab-pane>
                <el-tab-pane label="$_GET" name="tab-get">
                    <pre class="prettyprint linenums"><?php print_r($_GET) ?></pre>
                </el-tab-pane>
                <el-tab-pane label="$_POST" name="tab-post">
                    <pre class="prettyprint linenums"><?php print_r($_POST) ?></pre>
                </el-tab-pane>
                <el-tab-pane label="$_REQUEST" name="tab-request">
                    <pre class="prettyprint linenums"><?php print_r($_REQUEST) ?></pre>
                </el-tab-pane>
                <el-tab-pane label="$_COOKIE" name="tab-cookie">
                    <pre class="prettyprint linenums"><?php print_r($_COOKIE) ?></pre>
                </el-tab-pane>
            </el-tabs>
            <?php
        } else {
            ?>
            <div class="exception-icon">
                <i class="el-icon-warning"></i>
            </div>

            <div class="exception-message">
                <?php echo $this->e->getMessage(); ?>
            </div>
            <?php
        }
        ?>

    </div>

    <script>
        new Vue({
            el: '#app',
            data: {
                activeTab: 'tab-trace'
            },
            created: function () {
                prettyPrint();
            }
        });
    </script>

</be-body>
