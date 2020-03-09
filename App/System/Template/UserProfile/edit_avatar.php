<?php
use Be\System\Be;
?>
<!--{head}-->
<link type="text/css" rel="stylesheet" href="/app/System/Template/User/css/dashboard.css">
<script type="text/javascript" language="javascript" src="/app/System/Template/User/js/dashboard.js"></script>

<script type="text/javascript" language="javascript" src="/app/System/Template/userProfile/js/editAvatar.js"></script>
<!--{/head}-->


<!--{middle}-->
<div class="theme-west">
    <div class="wrapper">
        <!--{west}-->
        <!-- {include: System.UserProfile.west} -->
        <!--{/west}-->
    </div>
</div>
<div class="theme-center">
    <div class="wrapper">
        <!--{center}-->
        <?php
        $configSystem = Be::getConfig('System', 'System');
        $configUser = Be::getConfig('System', 'User');
        $my = Be::getUser();
        ?>
        <div class="theme-box-container">
            <div class="theme-box">
                <div class="theme-box-title"><?php echo $this->title; ?></div>
                <div class="theme-box-body">

                    <form action="<?php echo url('controller=userProfile&action=editAvatarSave'); ?>" method="post" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-5">
                                <div class="key">当前头像: </div>
                            </div>
                            <div class="col-15">
                                <div class="val">
                                    <img src="<?php echo url().'/'.DATA.'/user/avatar/'.($my->avatarL == ''?('default/'.$configUser->defaultAvatarL):$my->avatarL); ?>" />
                                    <?php
                                    $configUser = Be::getConfig('System', 'User');
                                    if ($my->avatarL != '') {
                                        ?>
                                        <a href="<?php echo url('controller=userProfile&action=initAvatar'); ?>" style="font-size:18px;">&times;</a>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="clear-left"></div>
                        </div>
                        <div class="row">
                            <div class="col-5"><div class="key">上传新头像：</div></div>
                            <div class="col-15"><input type="file" name="avatar" /></div>
                            <div class="clear-left"></div>
                        </div>
                        <div class="row">
                            <div class="col-5"></div>
                            <div class="col-15">
                                <div class="val">
                                    <p class="text-muted">允许上传的图像类型: <?php echo implode(', ', $configSystem->allowUploadImageTypes); ?></p>
                                    <p class="text-muted">图像大小: <?php echo $configUser->avatarLW; ?>px &times; <?php echo $configUser->avatarLH; ?>px</p>
                                </div>
                            </div>
                            <div class="clear-left"></div>
                        </div>

                        <div class="row">
                            <div class="col-5"></div>
                            <div class="col-15">
                                <div class="val">
                                    <input type="submit" class="btn btn-primary btn-submit" value="提交">
                                    <input type="reset" class="btn" value="重置">
                                </div>
                            </div>
                            <div class="clear-left"></div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
        <!--{/center}-->
    </div>
</div>
<!--{/middle}-->



