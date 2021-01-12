<style>
    .text {
        font-size: 14px;
    }

    .item {
        margin-bottom: 18px;
    }

    .clearfix:before,
    .clearfix:after {
        display: table;
        content: "";
    }
    .clearfix:after {
        clear: both
    }

    .box-card {
        width: 300px;
        font-size: 11px;
    }
</style>

<div id="app" v-cloak>

    <el-row>
    <?php foreach($this->tables as $table) { ?>
        <el-col :span="4">
            <el-card class="box-card">
                <div slot="header" class="clearfix">
                    <span><?php echo $table->name; ?></span><br />
                    <span><?php echo $table->comment; ?></span>
                </div>

                <?php
                foreach($table->fields as $field) {
                    echo $field['name'];
                    if (isset($field['comment'])) {
                        echo ': ' . $field['comment'];
                    }
                    echo '<br />';
                }
                ?>
            </el-card>
        </el-col>
    <?php } ?>
    </el-row>

</div>

<script>
    var vue = new Vue({
        el: '#app',
        data: {

        },
        methods: {

        }
    });
</script>
