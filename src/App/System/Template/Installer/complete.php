<be-head>
    <link type="text/css" rel="stylesheet" href="<?php echo \Be\System\Be::getProperty('App.System')->getUrl(); ?>/Template/Installer/css/complete.css">
</be-head>

<be-center>
    <div id="app" v-cloak>
        <div class="success-icon">
            <i class="el-icon-success"></i>
        </div>

        <div class="success-message">
            安装完成！
        </div>
    </div>
    <script>
        new Vue({
            el: '#app',
            data: {}
        });
    </script>
</be-center>
