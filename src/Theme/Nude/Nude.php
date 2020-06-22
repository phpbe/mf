<?php
use Be\System\Be;
?>
<be-html>
<?php
$my = Be::getUser();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title><?php echo $this->title; ?></title>

    <script src="https://unpkg.com/vue@2.6.11/dist/vue.min.js"></script>

    <script src="https://unpkg.com/axios@0.19.0/dist/axios.min.js"></script>
    <script>Vue.prototype.$http = axios;</script>

    <script src="https://unpkg.com/vue-cookies@1.5.13/vue-cookies.js"></script>

    <link rel="stylesheet" href="https://unpkg.com/element-ui@2.13.2/lib/theme-chalk/index.css">
    <script src="https://unpkg.com/element-ui@2.13.2/lib/index.js"></script>

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