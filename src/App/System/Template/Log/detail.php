<be-head>
    <link type="text/css" rel="stylesheet"
          href="<?php echo \Be\System\Be::getProperty('App.System')->getUrl(); ?>/Template/System/css/exception.css">
    <link rel="stylesheet"
          href="<?php echo \Be\System\Be::getProperty('App.System')->getUrl(); ?>/Template/System/google-code-prettify/prettify.css"
          type="text/css"/>
    <script type="text/javascript" language="javascript"
            src="<?php echo \Be\System\Be::getProperty('App.System')->getUrl(); ?>/Template/System/google-code-prettify/prettify.js"></script>
    <style type="text/css">
        .prettyprint {
            background-color: #fff;
            color: #000;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
</be-head>

<be-body>

    <div id="app" v-cloak>

        <el-tabs v-model="activeTab">
            <el-tab-pane label="错误基本信息" name="tab-base">
                错误编号：<?php echo $this->log['extra']['hash']; ?><br/>
                文件：<?php echo $this->log['context']['file']; ?><br/>
                行号：<?php echo $this->log['context']['line']; ?><br/>
                错误码：<?php echo $this->log['context']['code']; ?><br/>
                错误信息：<?php echo $this->log['message']; ?><br/>
            </el-tab-pane>
            <el-tab-pane label="错误跟踪信息" name="tab-trace">
                <pre class="prettyprint linenums"><?php print_r($this->log['context']['trace']); ?></pre>
            </el-tab-pane>
            <el-tab-pane label="$_SERVER" name="tab-server">
                <pre class="prettyprint linenums"><?php print_r($this->log['extra']['server']); ?></pre>
            </el-tab-pane>
            <el-tab-pane label="$_GET" name="tab-get">
                <pre class="prettyprint linenums"><?php print_r($this->log['extra']['get']); ?></pre>
            </el-tab-pane>
            <el-tab-pane label="$_POST" name="tab-post">
                <pre class="prettyprint linenums"><?php print_r($this->log['extra']['post']); ?></pre>
            </el-tab-pane>
            <el-tab-pane label="$_REQUEST" name="tab-request">
                <pre class="prettyprint linenums"><?php print_r($this->log['extra']['request']); ?></pre>
            </el-tab-pane>
            <el-tab-pane label="$_COOKIE" name="tab-cookie">
                <pre class="prettyprint linenums"><?php print_r($this->log['extra']['cookie']); ?></pre>
            </el-tab-pane>
        </el-tabs>

    </div>

    <script>
        new Vue({
            el: '#app',
            data: {
                activeTab: 'tab-base'
            },
            created: function () {
                prettyPrint();
            }
        });
    </script>

</be-body>
