<!--{head}-->
<script>



</script>
<!--{/head}-->


<!--{center}-->
<?php
$primaryKey = $this->table->getPrimaryKey();
?>
<div id="app" v-cloak>

<div class="curd-lists">

    <a-form :form="be_form" layout="inline" @submit="handleSubmit">


        <?php
        $search = null;
        if (isset($this->config['search']['items']) && count($this->config['search']['items']) > 0) {
            $search = $this->config['search']['items'];
        }

        $toolbar = null;
        if (isset($this->config['toolbar']['items']) && count($this->config['toolbar']['items']) > 0) {
            $toolbar = $this->config['toolbar']['items'];
        }

        if ($search || $toolbar) {
            ?>
            <table>
                <tr>
                    <td align="left">
                        <?php
                        if ($search) {
                            ?>
                            <div class="curd-lists-search">

                                <div class="row" style="margin-bottom: 5px;">
                                    <div class="clearfix">
                                        <?php
                                        $colCount = 0;
                                        foreach ($search as $key => $x) {
                                            $cols = 3;
                                            if (isset($x['cols'])) {
                                                $cols = $x['cols'];
                                            }

                                            $colCount += $cols;
                                            if ($colCount > 12) {
                                                $colCount = $cols;
                                                echo '</div></div><div class="row" style="margin-bottom: 5px;"><div class="clearfix">';
                                            }

                                            $driver = $x['driver'];
                                            $searchDriver = new $driver($key, '', $x);

                                            echo '<div class="col-md-'.$cols.'">';
                                            echo $searchDriver->getEditHtml();
                                            echo '</div>';
                                        }
                                        ?>


                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </td>
                    <?php
                    if ($toolbar) {
                        foreach ($toolbar as $x) {
                            ?>
                            <td width="46">
                                <div class="curd-lists-toolbar">
                                    <a class="<?php echo isset($x['class'])?$x['class']:''; ?>" style="<?php echo isset($x['style'])?$x['style']:''; ?>" href="<?php echo $x['url']; ?>" title="<?php echo $x['name']; ?>" data-toggle="tooltip">
                                        <span class="curd-lists-toolbar-icon <?php echo isset($x['icon'])?$x['icon']:''; ?>"></span>
                                        <span class="curd-lists-toolbar-name"><?php echo $x['name']; ?></span>
                                    </a>
                                </div>
                            </td>
                            <?php
                        }
                    }
                    ?>
                </tr>
            </table>
            <?php
        }
        ?>

        <div class="curd-lists-data">



        </div>

    </a-form>
</div>
</div>


<script>
    var app = new Vue({
        el: '#app',
        data: function() {
            return {
                be_saving: false,
                be_form: this.$form.createForm(this)
            };
        },
        methods: {
            handleSubmit: function (e) {
                e.preventDefault();

                var _this = this;
                this.be_form.validateFields(function(err, values){
                    if (!err) {
                        _this.be_saving = true;
                        _this.$http.post("<?php echo $this->url; ?>", values)
                            .then(function (response) {
                                _this.be_saving = false;
                                if (response.status == 200) {
                                    if (response.data.success) {
                                        _this.$message.success(response.data.message);
                                    } else {
                                        _this.$message.error(response.data.message);
                                    }
                                }
                            })
                            .catch(function (error) {
                                _this.be_saving = false;
                                _this.$message.error(error);
                            });
                    }
                });
            },
        },

        mounted: function () {
        }

    });

    //console.log(app);
</script>
<!--{/center}-->
