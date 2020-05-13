<?php
use Be\System\Be;
?>

<be-head>
<?php
$log = $this->log;
if (strlen(serialize($log)) < 100 * 1024) {
    ?>
    <link rel="stylesheet" href="<?php echo Be::getProperty('App.System')->path; ?>/AdminTemplate/System/google-code-prettify/prettify.css" type="text/css"/>
    <script type="text/javascript" language="javascript" src="<?php echo Be::getProperty('App.System')->path; ?>/AdminTemplate/System/google-code-prettify/prettify.js"></script>
    <script type="text/javascript">
        $().ready(function () {
            prettyPrint();
        });
    </script>
    <?php
}
?>

<style type="text/css">
    .prettyprint {
        background-color: #fff;color:#000;white-space: pre-wrap;word-wrap: break-word;
    }
</style>
</be-head>

<be-center>
<?php
$log = $this->log;

$items = [
    'trace' => '跟踪信息',
    'get' => '$_GET',
    'post' => '$_POST',
    'request' => '$_REQUEST',
    'session' => '$_SESSION',
    'cookie' => '$_COOKIE',
    'server' => '$_SERVER'
];
?>
<ul class="nav nav-tabs">
    <li class="active">
        <a href="#tab-base" data-toggle="tab"><span>基本信息</span></a>
    </li>

    <?php
    foreach ($items as $key => $val) {
        if (isset($log['extra'][$key])) {
            ?>
            <li>
                <a href="#tab-<?php echo $key; ?>" data-toggle="tab"><span><?php echo $val; ?></span></a>
            </li>
            <?php
        }
    }
    ?>
</ul>


<div class="tab-content" style="padding: 0 10px">

    <div class="tab-pane active" id="tab-base">
        文件：<?php echo $log['file']; ?><br />
        行号：<?php echo $log['line']; ?><br />
        错误信息：<?php echo $log['message']; ?><br />
        记录时间：<?php echo $log['record_time']; ?><br />
        <?php
        if (isset($log['extra']['memory_usage'])) {
            echo '内存使用：' . $log['extra']['memory_usage'] . '<br />';
        }

        if (isset($log['extra']['memory_peak_usage'])) {
            echo '内存使用峰值：' . $log['extra']['memory_peak_usage'] . '<br />';
        }
        ?>
    </div>

    <?php
    foreach ($items as $key => $val) {
        if (isset($log['extra'][$key])) {
            ?>
            <div class="tab-pane" id="tab-<?php echo $key; ?>">
                <pre class="prettyprint linenums"><?php print_r($log['extra'][$key]); ?></pre>
            </div>
            <?php
        }
    }
    ?>
</div>
</be-center>