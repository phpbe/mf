<?php
use Be\System\Be;
use Be\System\Session;
?>

<be-html>
<?php
$config = Be::getConfig('System.System');
$my = Be::getUser();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title><?php echo $this->title . ' - ' . $config->siteName; ?></title>

    <script src="https://unpkg.com/vue@2.6.11/dist/vue.min.js"></script>

    <script src="https://unpkg.com/axios@0.19.0/dist/axios.min.js"></script>
    <script>Vue.prototype.$http = axios;</script>

    <script src="https://unpkg.com/vue-cookies@1.5.13/vue-cookies.js"></script>

    <link rel="stylesheet" href="https://unpkg.com/element-ui@2.13.2/lib/theme-chalk/index.css">
    <script src="https://unpkg.com/element-ui@2.13.2/lib/index.js"></script>

    <link rel="stylesheet" href="<?php echo Be::getProperty('Theme.Admin')->getUrl(); ?>/css/theme.css" />
    <be-head>
    </be-head>
</head>
<body>
    <be-body>
    <div class="be-body">

        <div id="app-west" :class="{'be-west': true, 'be-west-collapsed': collapsed}" v-cloak>
            <be-west>

            <div class="logo">
                LOGO
            </div>

            <div class="west-menu">
                <?php
                $menu = Be::getMenu('Admin');
                $menuTree = $menu->getMenuTree()
                ?>
                <el-menu
                        :defaultSelectedKeys="['1']"
                        mode="inline"
                        theme="dark"
                        :inline-collapsed="collapsed">
                    <?php
                    $appName = Be::getRuntime()->getAppName();
                    foreach ($menuTree as $menu) {

                        if ($menu->id == $appName) {
                            // 有子菜单
                            if ($menu->subMenu) {
                                foreach ($menu->subMenu as $subMenu) {
                                    echo '<el-sub-menu key="west-menu-'.$subMenu->id.'">';
                                    echo '<span slot="title"><el-icon type="'.$subMenu->icon.'"></el-icon><span>'.$subMenu->label.'</span></span>';
                                    if ($subMenu->subMenu) {
                                        foreach ($subMenu->subMenu as $subSubMenu) {
                                            echo '<el-menu-item key="west-menu-'.$subSubMenu->id.'">';
                                            echo '<a href="'.$subSubMenu->url.'">'.'<el-icon type="'.$subSubMenu->icon.'"></el-icon>'.$subSubMenu->label.'</a>';
                                            echo '</el-menu-item>';
                                        }
                                    }
                                    echo '</el-sub-menu>';
                                }
                            }
                            break;
                        }
                    }
                    ?>
                </el-menu>
            </div>

            <div class="toggle" @click="toggleMenu">
                <el-icon :type="collapsed ?'caret-right': 'caret-left'"></el-icon>
            </div>

            </be-west>
        </div>


        <div class="be-middle" id="be-middle">
            <be-middle>

            <div class="be-north">
                <be-north>


                <div class="menu">

                    <div id="north-menu" v-cloak>
                        <?php
                        $menu = Be::getMenu('Admin');
                        $menuTree = $menu->getMenuTree();
                        ?>
                        <el-menu v-model="current"
                                mode="horizontal">

                            <el-menu-item key="home">
                                <el-icon type="home"></el-icon>后台首页
                            </el-menu-item>

                            <?php
                            foreach ($menuTree as $menu) {

                                // 有子菜单
                                if ($menu->subMenu) {
                                    echo '<el-sub-menu key="north-menu-'.$menu->id.'">';
                                    echo '<span slot="title">';
                                    echo '<el-icon type="'.$menu->icon.'"></el-icon>'.$menu->label;
                                    echo '</span>';
                                    foreach ($menu->subMenu as $subMenu) {
                                        echo '<el-sub-menu key="north-menu-'.$subMenu->id.'">';
                                        echo '<span slot="title"><el-icon type="'.$subMenu->icon.'"></el-icon><span>'.$subMenu->label.'</span></span>';
                                        if ($subMenu->subMenu) {
                                            foreach ($subMenu->subMenu as $subSubMenu) {
                                                echo '<el-menu-item key="north-menu-'.$subSubMenu->id.'">';
                                                echo '<a href="'.$subSubMenu->url.'">'.'<el-icon type="'.$subSubMenu->icon.'"></el-icon>'.$subSubMenu->label.'</a>';
                                                echo '</el-menu-item>';
                                            }
                                        }
                                        echo '</el-sub-menu>';
                                    }
                                    echo '</el-sub-menu>';
                                }
                            }
                            ?>

                            <el-sub-menu>
                                <span slot="title">
                                    <el-icon type="info-circle"></el-icon>帮助
                                </span>
                                <el-menu-item key="help-official">
                                    <a href="http://www.phpbe.com/" target="_blank"><el-icon type="global"></el-icon>官方网站</a>
                                </el-menu-item>
                                <el-menu-item key="help-support">
                                    <a href="http://support.phpbe.com/" target="_blank"><el-icon type="bulb"></el-icon>技术支持</a>
                                </el-menu-item>
                            </el-sub-menu>

                        </el-menu>

                    </div>

                </div>

                <div class="user">
                    <?php
                    $configUser = Be::getConfig('System.User');
                    ?>
                    您好：
                    <img src="<?php echo Be::getRuntime()->getDataUrl().'/adminUser/avatar/'.($my->avatarS == ''?('default/'.$configUser->defaultAvatarS):$my->avatarS); ?>" style="max-width:24px;max-height:24px;" />
                    <?php echo $my->name; ?> &nbsp; &nbsp;
                    <a href="<?php echo beUrl('System.User.logout')?>" class="btn btn-warning btn-small"><i class="icon-white icon-off"></i> 退出</a>
                </div>

                </be-north>
            </div>

            <div class="be-center">
                <div class="center-title" id="app-center-title" v-cloak>
                    <?php
                    $menu = Be::getMenu('Admin');
                    $pathway = $menu->getPathwayByUrl(\Be\System\Request::url());
                    ?>
                    <el-breadcrumb>
                        <el-breadcrumb-item href="">
                            <el-icon type="home"></el-icon>
                        </el-breadcrumb-item>
                        <?php
                        foreach ($pathway as $x) {
                            ?>
                            <el-breadcrumb-item>
                                <span><?php echo $x->label; ?></span>
                            </el-breadcrumb-item>
                            <?php
                        }
                        ?>
                    </el-breadcrumb>
                </div>
                <div class="center-body">
                    <be-center>
                    </be-center>
                </div>
            </div>
            </be-middle>
        </div>

    </div>

    <script src="<?php echo Be::getProperty('Theme.Admin')->getUrl(); ?>/js/theme.js"></script>

    </be-body>
</body>
</html>
</be-html>