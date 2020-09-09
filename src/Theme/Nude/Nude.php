<?php
use Be\System\Be;
?>
<be-html>
<?php
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

    <style>
        body {background-color: #fff;}
        ::-webkit-scrollbar {width: 8px;}
        ::-webkit-scrollbar-thumb {background-color: #555;}
        [v-cloak] {display: none;}
        [class^="el-icon-fa"],
        [class*="el-icon-fa"] {
            display: inline-block;
            font-style: normal;
            font-variant: normal;
            font-weight: normal;
            font-family: FontAwesome!important;
            font-size: inherit;
            text-rendering: auto;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        .CodeMirror{
            font-size : 13px;
            line-height : 150%;
            border: 1px solid #DCDFE6;
            min-height: 60px;
            height: auto !important;
        }
    </style>

    <be-head>
    </be-head>
</head>
<body>
    <be-body>
    <div class="be-body">

        <be-center>
        </be-center>

    </div>
    </be-body>
</body>
</html>
</be-html>