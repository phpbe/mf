<be-head>
    <link type="text/css" rel="stylesheet" href="<?php echo \Be\Mf\Be::getProperty('App.System')->getUrl(); ?>/Template/Installer/css/complete.css">
</be-head>

<be-center>
    <div id="app" v-cloak>
        <el-form>
            <el-table :data="tableData" ref="tableRef" @selection-change="selectionChange">
                <el-table-column type="selection" width="50"></el-table-column>
                <el-table-column prop="icon" label="" width="50"></el-table-column>
                <el-table-column prop="name" label="名称" width="150"></el-table-column>
                <el-table-column prop="label" label="名称" width="150"></el-table-column>
                <el-table-column prop="description" label="描述"></el-table-column>
            </el-table>
            <el-button type="primary" icon="el-icon-search" @click="submit" :disabled="loading">查询</el-button>
        </el-form>
    </div>
    <script>
        new Vue({
            el: '#app',
            data: {
                formData:{
                    appNames: []
                },
                tableData: <?php echo json_encode($this->appProperties); ?>
            },
            methods: {
                selectionChange: function(rows) {
                    var arrAppNames = [];
                    for (var x in rows) {
                        var row = rows[i];
                        arrAppNames.push(row.name);
                    }
                    this.formData.appNames = arrAppNames;
                },
                submit: function () {
                    var eForm = document.createElement("form");
                    eForm.action = window.location.href;
                    eForm.target = "_self";
                    eForm.method = "post";
                    eForm.style.display = "none";

                    var e = document.createElement("textarea");
                    e.name = 'data';
                    e.value = JSON.stringify({formData: this.formData});
                    eForm.appendChild(e);

                    document.body.appendChild(eForm);

                    setTimeout(function () {
                        eForm.submit();
                    }, 50);

                    setTimeout(function () {
                        document.body.removeChild(eForm);
                    }, 3000);
                }
            },
            created: function () {
                for(var x in this.tableData) {
                    this.$refs.tableRef.selectionChange(this.tableData[x]);
                }
            }
        });
    </script>
</be-center>
