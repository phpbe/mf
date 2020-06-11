<be-html>
<ul>
    <?php
    foreach($this->tables as $table) {
        ?>
        <li data-url="<?php echo beUrl('System.Db.tables', ['type'=>'config', 'app'=>$this->app, 'table' => $table->tableName]); ?>">><?php echo $table->tableName; ?></li>
        <?php
    }
    ?>
</ul>
</be-html>