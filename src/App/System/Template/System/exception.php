<be-head>
    <link type="text/css" rel="stylesheet" href="<?php echo \Be\System\Be::getProperty('App.System')->getUrl(); ?>/Template/System/css/exception.css">
    <link rel="stylesheet" href="<?php echo Be\System\Be::getProperty('App.System')->getUrl(); ?>/Template/System/google-code-prettify/prettify.css" type="text/css"/>
    <script type="text/javascript" language="javascript" src="<?php echo Be\System\Be::getProperty('App.System')->getUrl(); ?>/Template/System/google-code-prettify/prettify.js"></script>
    <style type="text/css">
        .prettyprint {
            background-color: #fff;color:#000;white-space: pre-wrap;word-wrap: break-word;
        }
    </style>
</be-head>

<be-body>

    <div id="app" v-cloak>
        <div class="exception-icon">
            <i class="el-icon-warning"></i>
        </div>

        <div class="exception-message">
            <?php
            if (isset($this->e)) {
                echo $this->e->getMessage();

            } else {
                ?>
                出错啦！
                <?php
            }
            ?>
        </div>

        <?php
        if (isset($this->e)) {
            ?>
            <div class="exception-trace">
                <pre class="prettyprint linenums">
                    <?php
                    if (isset($this->e)) {
                        print_r($this->e->getTrace());
                    }
                    ?>
                </pre>
            </div>
            <?php
        }
        ?>

    </div>

    <script>
        new Vue({
            el: '#app',
            created: function () {
                prettyPrint();
            }
        });
    </script>

</be-body>
