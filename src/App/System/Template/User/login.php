<?php
use Be\System\Be;
use Be\System\Request;
?>
<!--{head}-->
<link type="text/css" rel="stylesheet" href="<?php echo Be::getProperty('App.System')->path; ?>/Template/User/css/login.css" />
<!--{/head}-->

<!--{body}-->
<?php
$config = Be::getConfig('System.System');
?>
<div id="app">

    <div class="logo"></div>

    <div class="login-box">
        <a-form size="small" layout="horizontal">
            <a-form-item label="用户名" :label-col="{span:6}" :wrapper-col="{span:18}">
                <a-input v-model="formData.username" placeholder="用户名">
                    <a-icon slot="prefix" type="user"></a-icon>
                    <a-icon v-if="formData.username" slot="suffix" type="close-circle" @click="formData.username=''"></a-icon>
                </a-input>
            </a-form-item>
            <a-form-item label="密码" :label-col="{span:6}" :wrapper-col="{span:18}">
                <a-input v-model="formData.password" placeholder="密码" :type="passwordType" ref="passwordInput">
                    <a-icon slot="prefix" type="lock"></a-icon>
                    <a-icon v-if="formData.password" slot="suffix" type="close-circle" @click="formData.password=''"></a-icon>
                    <a-icon slot="suffix" :type="togglePasswordIcon" @click="togglePassword"></a-icon>
                </a-input>
            </a-form-item>
            <a-form-item :wrapper-col="{offset:6}">
                <a-button type="primary" @click="login" :loading="loginLoading">
                    <a-icon type="unlock"></a-icon>登陆
                </a-button>
            </a-form-item>
        </a-form>
    </div>

</div>

<?php
$return = Request::get('return', '');
if ($return=='') {
    $return = url('System.System.dashboard');
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
            loginLoading: false,
            passwordType: "password",
            togglePasswordIcon: "eye"
        },
        methods: {
            togglePassword: function() {
                if (this.passwordType == "password") {
                    this.passwordType = "text";
                    this.togglePasswordIcon = "eye-invisible";
                } else {
                    this.passwordType = "password";
                    this.togglePasswordIcon = "eye";
                }
            },

            login: function() {
                var _this = this;
                _this.loginLoading = true;
                this.$http.post("<?php echo url('System.User.login'); ?>", _this.formData)
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

<!--{/body}-->