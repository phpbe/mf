<?php
use Be\System\Be;
?>

<be-head>

<script type="text/javascript" language="javascript">

</script>
<script type="text/javascript" language="javascript" src="<?php echo Be::getProperty('App.System')->getUrl(); ?>/AdminTemplate/Db/js/tables.js"></script>
</be-head>

<be-center>
<div id="apps">
    <ul>
        <?php
        foreach($this->apps as $app => $name) {
            ?>
            <li data-url="<?php echo beUrl('System.Db.tables', ['type' => 'lists', 'app' => $app]); ?>">><?php echo $name; ?>（<?php echo $app; ?>）</li>
            <?php
        }
        ?>
    </ul>
</div>
<div id="tables"></div>
<div id="table-config"></div>
</be-center>