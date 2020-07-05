<?php
use Be\System\Be;
use Be\System\Request;
?>
<be-head>
<link type="text/css" rel="stylesheet" href="<?php echo Be::getProperty('App.System')->getUrl(); ?>/Template/User/css/login.css" />
</be-head>

<be-body>
<?php
$config = Be::getConfig('System.System');
?>
<div id="app">

    <div class="logo"></div>

    <div class="login-box">
        <el-form size="small" layout="horizontal">
            <el-form-item label="用户名" :label-col="{span:6}" :wrapper-col="{span:18}">
                <el-input v-model="formData.username" placeholder="用户名" prefix-icon="el-icon-search"></el-input>
            </el-form-item>
            <el-form-item label="密码" :label-col="{span:6}" :wrapper-col="{span:18}">
                <el-input v-model="formData.password" placeholder="密码" prefix-icon="el-icon-search" show-password></el-input>
            </el-form-item>
            <el-form-item :wrapper-col="{offset:6}">
                <el-button type="primary" @click="login" :loading="loginLoading">
                    <el-icon type="unlock"></el-icon>登陆
                </el-button>
            </el-form-item>
        </el-form>
    </div>

</div>

<?php
$return = Request::get('return', '');
if ($return=='') {
    $return = beUrl('System.System.dashboard');
} else {
    $return = base64_decode($return);
}
?>
<script>
    new Vue({
        el: '#app',
        data: {
            formData: {
                username : "",
                password : ""
            },
            loginLoading: false
        },
        methods: {
            login: function() {
                var _this = this;
                _this.loginLoading = true;
                this.$http.post("<?php echo beUrl('System.User.login'); ?>", _this.formData)
                    .then(function (response) {
                        _this.loginLoading = false;
                        if (response.status == 200) {
                            if (response.data.success) {
                                window.location.href = "<?php echo $return; ?>";
                            } else {
                                _this.$message.error(response.data.message);
                            }
                        }
                    })
                    .catch(function (error) {
                        _this.loginLoading = false;
                        _this.$message.error(error);
                    });

            }
        }
    });
</script>

</be-body>