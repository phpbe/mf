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

    <base href="<?php echo url(); ?>/" />

    <script src="https://unpkg.com/vue@2.6.10/dist/vue.min.js"></script>

    <script src="https://unpkg.com/axios@0.19.0/dist/axios.min.js"></script>
    <script>Vue.prototype.$http = axios;</script>

    <script src="https://unpkg.com/vue-cookies@1.5.13/vue-cookies.js"></script>

    <link rel="stylesheet" href="https://unpkg.com/ant-design-vue@1.3.16/dist/antd.min.css">
    <script src="https://unpkg.com/ant-design-vue@1.3.16/dist/antd.min.js"></script>

    <link rel="stylesheet" href="<?php echo Be::getProperty('Theme.Admin')->path; ?>/css/theme.css" />
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
                <a-menu
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
                                    echo '<a-sub-menu key="west-menu-'.$subMenu->id.'">';
                                    echo '<span slot="title"><a-icon type="'.$subMenu->icon.'"></a-icon><span>'.$subMenu->label.'</span></span>';
                                    if ($subMenu->subMenu) {
                                        foreach ($subMenu->subMenu as $subSubMenu) {
                                            echo '<a-menu-item key="west-menu-'.$subSubMenu->id.'">';
                                            echo '<a href="'.$subSubMenu->url.'">'.'<a-icon type="'.$subSubMenu->icon.'"></a-icon>'.$subSubMenu->label.'</a>';
                                            echo '</a-menu-item>';
                                        }
                                    }
                                    echo '</a-sub-menu>';
                                }
                            }
                            break;
                        }
                    }
                    ?>
                </a-menu>
            </div>

            <div class="toggle" @click="toggleMenu">
                <a-icon :type="collapsed ?'caret-right': 'caret-left'"></a-icon>
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
                        <a-menu v-model="current"
                                mode="horizontal">

                            <a-menu-item key="home">
                                <a-icon type="home"></a-icon>后台首页
                            </a-menu-item>

                            <?php
                            foreach ($menuTree as $menu) {

                                // 有子菜单
                                if ($menu->subMenu) {
                                    echo '<a-sub-menu key="north-menu-'.$menu->id.'">';
                                    echo '<span slot="title">';
                                    echo '<a-icon type="'.$menu->icon.'"></a-icon>'.$menu->label;
                                    echo '</span>';
                                    foreach ($menu->subMenu as $subMenu) {
                                        echo '<a-sub-menu key="north-menu-'.$subMenu->id.'">';
                                        echo '<span slot="title"><a-icon type="'.$subMenu->icon.'"></a-icon><span>'.$subMenu->label.'</span></span>';
                                        if ($subMenu->subMenu) {
                                            foreach ($subMenu->subMenu as $subSubMenu) {
                                                echo '<a-menu-item key="north-menu-'.$subSubMenu->id.'">';
                                                echo '<a href="'.$subSubMenu->url.'">'.'<a-icon type="'.$subSubMenu->icon.'"></a-icon>'.$subSubMenu->label.'</a>';
                                                echo '</a-menu-item>';
                                            }
                                        }
                                        echo '</a-sub-menu>';
                                    }
                                    echo '</a-sub-menu>';
                                }
                            }
                            ?>

                            <a-sub-menu>
                                <span slot="title">
                                    <a-icon type="info-circle"></a-icon>帮助
                                </span>
                                <a-menu-item key="help-official">
                                    <a href="http://www.phpbe.com/" target="_blank"><a-icon type="global"></a-icon>官方网站</a>
                                </a-menu-item>
                                <a-menu-item key="help-support">
                                    <a href="http://support.phpbe.com/" target="_blank"><a-icon type="bulb"></a-icon>技术支持</a>
                                </a-menu-item>
                            </a-sub-menu>

                        </a-menu>

                    </div>

                </div>

                <div class="user">
                    <?php
                    $configUser = Be::getConfig('System.User');
                    ?>
                    您好：
                    <img src="<?php echo Be::getRuntime()->getDataUrl().'/adminUser/avatar/'.($my->avatarS == ''?('default/'.$configUser->defaultAvatarS):$my->avatarS); ?>" style="max-width:24px;max-height:24px;" />
                    <?php echo $my->name; ?> &nbsp; &nbsp;
                    <a href="<?php echo url('System.User.logout')?>" class="btn btn-warning btn-small"><i class="icon-white icon-off"></i> 退出</a>
                </div>

                </be-north>
            </div>

            <div class="be-center">
                <div class="center-title" id="app-center-title" v-cloak>
                    <?php
                    $menu = Be::getMenu('Admin');
                    $pathway = $menu->getPathwayByUrl(\Be\System\Request::url());
                    ?>
                    <a-breadcrumb>
                        <a-breadcrumb-item href="">
                            <a-icon type="home"></a-icon>
                        </a-breadcrumb-item>
                        <?php
                        foreach ($pathway as $x) {
                            ?>
                            <a-breadcrumb-item>
                                <span><?php echo $x->label; ?></span>
                            </a-breadcrumb-item>
                            <?php
                        }
                        ?>
                    </a-breadcrumb>
                </div>
                <div class="center-body">
                    <be-center>
                    </be-center>
                </div>
            </div>
            </be-middle>
        </div>

    </div>

    <script src="<?php echo Be::getProperty('Theme.Admin')->path; ?>/js/theme.js"></script>

    </be-body>
</body>
</html>
</be-html>