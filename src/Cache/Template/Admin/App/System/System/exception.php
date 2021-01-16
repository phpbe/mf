<?php
namespace Be\Mf\Cache\Template\Admin\App\System\System;

use Be\Mf\Be;

class exception extends \Be\F\Template\Driver
{

  public function display()
  {

    ?>
<?php
$config = Be::getConfig('System.System');
$my = Be::getUser();
$themeUrl = Be::getProperty('Theme.Admin')->getUrl();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title><?php echo $this->title; ?></title>

    <script src="<?php echo $themeUrl; ?>/js/vue-2.6.11.min.js"></script>

    <script src="<?php echo $themeUrl; ?>/js/axios-0.19.0.min.js"></script>
    <script>Vue.prototype.$http = axios;</script>

    <script src="<?php echo $themeUrl; ?>/js/vue-cookies-1.5.13.js"></script>

    <link rel="stylesheet" href="<?php echo $themeUrl; ?>/css/element-ui-2.13.2.css">
    <script src="<?php echo $themeUrl; ?>/js/element-ui-2.13.2.js"></script>

    <link rel="stylesheet" href="<?php echo $themeUrl; ?>/css/font-awesome-4.7.0.min.css" />

    <link rel="stylesheet" href="<?php echo $themeUrl; ?>/css/theme.css" />
    
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

</head>
<body>
    

    <div id="app" v-cloak>
        <?php
        $configSystem = \Be\Mf\Be::getConfig('System.System');
        if ($configSystem->developer) {
            $request = \Be\Mf\Be::getRequest();
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
                    <pre class="prettyprint linenums"><?php print_r($request->server()) ?></pre>
                </el-tab-pane>
                <el-tab-pane label="$_GET" name="tab-get">
                    <pre class="prettyprint linenums"><?php print_r($request->get()) ?></pre>
                </el-tab-pane>
                <el-tab-pane label="$_POST" name="tab-post">
                    <pre class="prettyprint linenums"><?php print_r($request->post()) ?></pre>
                </el-tab-pane>
                <el-tab-pane label="$_REQUEST" name="tab-request">
                    <pre class="prettyprint linenums"><?php print_r($request->request()) ?></pre>
                </el-tab-pane>
                <el-tab-pane label="$_COOKIE" name="tab-cookie">
                    <pre class="prettyprint linenums"><?php print_r($request->cookie()) ?></pre>
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


</body>
</html>
    <?php
  }
}

