<?php
use Be\System\Be;
?>

<be-head>
<link type="text/css" rel="stylesheet" href="<?php echo Be::getProperty('App.System')->path; ?>/AdminTemplate/System/css/dashboard.css">
<script type="text/javascript" language="javascript" src="<?php echo Be::getProperty('App.System')->path; ?>/AdminTemplate/System/js/dashboard.js"></script>
</be-head>

<be-center>
<?php
$my = Be::getAdminUser();
$user = $this->user;
$recentLogs = $this->recentLogs;
$userCount = $this->userCount;
$appCount = $this->appCount;
$themeCount = $this->themeCount;

$configUser = Be::getConfig('System.User');
?>
<div id="app">
 <div class="row-fluid">
    <div class="span6">

        <div class="box">
            <div class="box-title">
                <table style="width:100%;">
                <tr>
                    <td style="text-align:left; "><i class="icon-user"></i> 管理员信息</td>
                    <td style="text-align:right; padding-right:10px;"><a href="./?controller=user&action=edit&id=<?php echo $my->id; ?>" title="修改当前管理员用户资料" data-toggle="tooltip"><i class="icon-pencil"></i></a></td>
                </tr>
                </table>
            </div>
            <div class="box-body" style="height:80px;">
            <table style="width:100%;">
            <tr>
                <td style="width:80px; text-align:left; "><img src="../<?php echo DATA.'/user/avatar/'.($user->avatarM == ''?('default/'.$configUser->defaultAvatarM):$user->avatarM); ?>" /></td>
                <td valign="top">
                    <p>您好 <span style="font-weight:bold; color:red;"><?php echo $user->name; ?></span>(<?php echo implode(', ', $my->getRoleNames()); ?>), 欢迎回来。</p>
                    <p class="muted">上次登陆时间：<?php echo date('Y-m-d H:i', $user->lastLoginTime); ?> [<a href="./?controller=user&action=logs" class="text-info">查看登陆日志</a>]</p>
                </td>
            </tr>
            </table>
            </div>
        </div>

    </div>
    <div class="span6">

        <div class="box">
            <div class="box-title">
                <i class="icon-info-sign"></i>
                <a href="http://www.phpbe.com/" target="Blank">BE</a>
            </div>
            <div class="box-body" style="height:80px;">

            <table style="width:100%;">
            <tr>
                <td style="width:33%; text-align:center; border-right:#ccc 1px solid; ">已安装的应用</td>
                <td style="width:33%; text-align:center; border-right:#ccc 1px solid; ">已安装的主题</td>
                <td style="width:33%; text-align:center; ">注册用户数</td>
            </tr>
            <tr>
                <td style="width:33%; text-align:center; border-right:#ccc 1px solid; padding-top:10px; height:50px;">
                    <a href="./?app=System&controller=System&action=apps" title="管理这 <?php echo $appCount; ?> 个应用" data-toggle="tooltip"  style="font-size:36px; " class="text-info">
                    <?php echo $appCount; ?>
                    </a>
                </td>
                <td style="width:33%; text-align:center; border-right:#ccc 1px solid;;padding-top:10px;">
                    <a href="./?app=System&controller=System&action=themes" title="管理这 <?php echo $themeCount; ?> 个主题" data-toggle="tooltip"  style="font-size:36px; " class="text-info"><?php echo $themeCount; ?></a>
                </td>
                <td style="width:33%; text-align:center; font-size:36px;padding-top:10px;">
                    <a href="./?controller=user&action=listing" title="管理这 <?php echo $userCount; ?> 个用户" data-toggle="tooltip"  style="font-size:36px; " class="text-info"><?php echo $userCount; ?></a>
                </td>
            </tr>
            </table>
            </div>
        </div>

    </div>
</div>


    <el-card class="box-card">
        <div slot="header" class="clearfix">
            <span>最近操作日志</span>
            <el-button style="float: right; padding: 3px 0" type="text" @click="window.location.href='<?php echo beUrl('System.AdminLogs.logs')?>'">更多..</el-button>
        </div>

        <el-table :data="recentLogs" stripe style="width: 100%;">
            <el-table-column
                    prop="create_time"
                    label="时间"
                    width="180">
                <template slot-scope="scope">
                    <div v-html="scope.row.create_time"></div>
                </template>
            </el-table-column>
            <el-table-column
                    prop="title"
                    label="操作">
            </el-table-column>
            <el-table-column
                    prop="ip"
                    label="IP"
                    width="180">
            </el-table-column>
            <el-table-column
                    prop="address"
                    label="地理位置">
            </el-table-column>
        </el-table>

    </el-card>

</div>

<?php
$libIp = Be::getLib('Ip');
$date = '';
foreach ($recentLogs as $log) {
    $newDate = date('Y-m-d',$log->create_time);
    if ($date == $newDate) {
        $log->create_time = '<span style="visibility:hidden;">'. $newDate .' &nbsp;</span>'. date('H:i:s',$log->create_time);
    } else {
        $log->create_time = $newDate .' &nbsp;'. date('H:i:s',$log->create_time);
        $date = $newDate;
    }
    $log->address = $libIp->convert($log->ip);
}
?>
<script>
    var vue = new Vue({
        el: '#app',
        data: {
            recentLogs : <?php echo json_encode($recentLogs); ?>
        },
        methods: {
        }
    });
</script>
</be-center>