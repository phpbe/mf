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
    <title><?php echo $this->title; ?></title>

    <script src="https://unpkg.com/vue@2.6.11/dist/vue.min.js"></script>

    <script src="https://unpkg.com/axios@0.19.0/dist/axios.min.js"></script>
    <script>Vue.prototype.$http = axios;</script>

    <script src="https://unpkg.com/vue-cookies@1.5.13/vue-cookies.js"></script>

    <link rel="stylesheet" href="https://unpkg.com/element-ui@2.13.2/lib/theme-chalk/index.css">
    <script src="https://unpkg.com/element-ui@2.13.2/lib/index.js"></script>

    <link rel="stylesheet" href="https://unpkg.com/font-awesome@4.7.0/css/font-awesome.min.css" />

    <link rel="stylesheet" href="<?php echo Be::getProperty('Theme.Admin')->getUrl(); ?>/css/theme.css" />
    <be-head>
    </be-head>
</head>
<body>
    <be-body>
    <div class="be-body">

        <div id="app-west" :class="{'be-west': true, 'be-west-collapse': collapse}" v-cloak>
            <be-west>

            <div class="logo">
                <a href="<?php echo beUrl(); ?>"></a>
            </div>

            <div class="west-menu">
                <?php
                $menu = Be::getMenu();
                $menuTree = $menu->getMenuTree()
                ?>
                <el-menu
                        background-color="#001529"
                        text-color="#ccc"
                        active-text-color="#ffd04b"
                        :default-active="activeIndex"
                        :collapse="collapse"
                        :collapse-transition="false">
                    <?php
                    $appName = Be::getRuntime()->getAppName();
                    foreach ($menuTree as $menu) {

                        if ($menu->id == $appName) {
                            // 有子菜单
                            if ($menu->subMenu) {
                                foreach ($menu->subMenu as $subMenu) {
                                    echo '<el-submenu index="west-menu-'.$subMenu->id.'">';

                                    echo '<template slot="title">';
                                    echo '<i class="'.$subMenu->icon.'"></i>';
                                    echo '<span slot="title">'.$subMenu->label.'</span>';
                                    echo '</template>';

                                    if ($subMenu->subMenu) {
                                        foreach ($subMenu->subMenu as $subSubMenu) {
                                            echo '<el-menu-item index="west-menu-'.$subSubMenu->id.'">';
                                            //echo '<el-link href="'.$subSubMenu->url.'">';
                                            echo '<i class="'.$subSubMenu->icon.'"></i>';
                                            echo '<span slot="title">'.$subSubMenu->label.'</span>';
                                            //echo '</el-link>';
                                            echo '</el-menu-item>';
                                        }
                                    }
                                    echo '</el-submenu>';
                                }
                            }
                            break;
                        }
                    }
                    ?>
                </el-menu>

            </div>

            <div class="toggle" @click="toggleMenu">
                <i :class="collapse ?'el-icon-s-unfold': 'el-icon-s-fold'"></i>
            </div>

            </be-west>
        </div>


        <div class="be-middle" id="be-middle">
            <be-middle>

            <div class="be-north" id="be-north">
                <be-north>

                <div class="menu">
                    <div v-cloak>
                        <?php
                        $menu = Be::getMenu();
                        $menuTree = $menu->getMenuTree();
                        ?>
                        <el-menu
                                mode="horizontal"
                                :default-active="defaultActive"
                                background-color="#eee"
                                text-color="#444"
                                active-text-color="#000">
                            <?php
                            foreach ($menuTree as $menu) {

                                // 有子菜单
                                if ($menu->subMenu) {
                                    echo '<el-submenu index="north-menu-'.$menu->id.'">';

                                    echo '<template slot="title">';
                                    echo '<i class="'.$menu->icon.'"></i>';
                                    echo '<span slot="title">'.$menu->label.'</span>';
                                    echo '</template>';

                                    foreach ($menu->subMenu as $subMenu) {
                                        echo '<el-submenu index="north-menu-'.$subMenu->id.'">';

                                        echo '<template slot="title">';
                                        echo '<i class="'.$subMenu->icon.'"></i>';
                                        echo '<span slot="title">'.$subMenu->label.'</span>';
                                        echo '</template>';

                                        if ($subMenu->subMenu) {
                                            foreach ($subMenu->subMenu as $subSubMenu) {
                                                echo '<el-menu-item key="north-menu-'.$subSubMenu->id.'">';
                                                echo '<template slot="title">';
                                                echo '<el-link href="'.$subSubMenu->url.'" icon="'.$subSubMenu->icon.'" :underline="false">';
                                                echo $subSubMenu->label;
                                                echo '</el-link>';
                                                echo '</template>';
                                                echo '</el-menu-item>';
                                            }
                                        }
                                        echo '</el-submenu>';
                                    }
                                    echo '</el-submenu>';
                                }
                            }
                            ?>

                            <el-submenu>
                                <template slot="title">
                                    <i class="el-icon-question"></i>
                                    <span slot="title">帮助</span>
                                </template>

                                <el-menu-item key="help-official">
                                    <el-link href="http://www.phpbe.com/" target="_blank" icon="el-icon-position" :underline="false">官方网站</el-link>
                                </el-menu-item>
                                <el-menu-item key="help-support">
                                    <el-link href="http://support.phpbe.com/" target="_blank" icon="el-icon-help" :underline="false">技术支持</el-link>
                                </el-menu-item>
                            </el-submenu>

                        </el-menu>

                    </div>

                </div>

                <div class="user">
                    <?php
                    $configUser = Be::getConfig('System.User');
                    ?>
                    您好：
                    <img src="<?php
                    if ($my->avatar_s == '') {
                        echo Be::getProperty('App.System')->getUrl().'/Template/User/images/avatar/medium.png';
                    } else {
                        echo Be::getRuntime()->getDataUrl().'/user/avatar/'.$my->avatar_m;
                    }
                    ?>" style="max-width:24px;max-height:24px;" />
                    <?php echo $my->name; ?>

                    <el-button type="danger" icon="el-icon-star-off" onclick="window.location.href='<?php echo beUrl('System.User.logout')?>';" size="mini">退出</el-button>

                </div>

                </be-north>
            </div>

            <div class="be-center">
                <div class="center-body">
                    <be-center>
                    </be-center>
                </div>
            </div>
            </be-middle>
        </div>

    </div>

    <script>

        var vueNorth = new Vue({
            el: '#be-north',
            data: {
                defaultActive: "<?php echo Be::getRuntime()->getAppName(); ?>",
                aboutModel: false
            },
            methods: {

            }
        });


        var sWestMenuCollapseKey = '_westMenuCollapse';
        var vueWestMenu = new Vue({
            el: '#app-west',
            data : {
                activeIndex: "<?php echo Be::getRuntime()->getAppName(); ?>",
                collapse: this.$cookies.isKey(sWestMenuCollapseKey) && this.$cookies.get(sWestMenuCollapseKey) == '1'
            },
            methods: {
                toggleMenu: function (e) {
                    this.collapse = !this.collapse;
                    console.log(this.collapse);
                    document.getElementById("be-middle").style.left = this.collapse ? "48px" : "200px";
                    this.$cookies.set(sWestMenuCollapseKey, this.collapse ? '1' : '0', 86400 * 180);
                }
            },
            created: function () {
                if (this.collapse) {
                    document.getElementById("be-middle").style.left = "48px";
                }
            }
        });

    </script>

    </be-body>
</body>
</html>
</be-html>