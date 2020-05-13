<?php
use Be\System\Be;
use Be\Util\Str;

?>
<be-head>
<?php
$uiGrid = Be::getUi('grid');
$uiGrid->head();
?>
</be-head>

<be-center>
<?php
$years = $this->years;
$year = $this->year;

$months = $this->months;
$month = $this->month;

$days = $this->days;
$day = $this->day;

$logs = $this->logs;
$logCount = $this->logCount;
?>
<ul>
    <?php
    if (count($years)) {
        ?>
        <li>
            <strong>年份：</strong>
            <?php
            foreach ($years as $x) {
                if ($x == $year) {
                    ?>
                    <span class="badge"><?php echo $x; ?></span>
                    <?php
                } else {
                    ?>
                    <a href="<?php echo url('System', 'SystemLog', 'logs', ['year' => $x]); ?>"><?php echo $x; ?></a>
                    <?php
                }
            }
            ?>
        </li>
        <?php
    }

    if (count($months)) {
        ?>
        <li>
            <strong>月份：</strong>
            <?php
            foreach ($months as $x) {
                if ($x == $month) {
                    ?>
                    <span class="badge"><?php echo $x; ?></span>
                    <?php
                } else {
                    ?>
                    <a href="<?php echo url('System', 'SystemLog', 'logs', ['year' => $year, 'month' => $x]); ?>"><?php echo $x; ?></a>
                    <?php
                }
            }
            ?>
        </li>
        <?php
    }

    if (count($days)) {
        ?>
        <li>
            <strong>日期：</strong>
            <?php
            foreach ($days as $x) {
                if ($x == $day) {
                    ?>
                    <span class="badge"><?php echo $x; ?></span>
                    <?php
                } else {
                    ?>
                    <a href="<?php echo url('System', 'SystemLog', 'logs', ['year' => $year, 'month' => $month, 'day' => $x]); ?>"><?php echo $x; ?></a>
                    <?php
                }
            }
            ?>
        </li>
        <?php
    }
    ?>
</ul>
<?php

if (count($logs)) {
    $uiGrid = Be::getUi('grid');

    $uiGrid->setAction('listing', url('System.System.logs'));

    $formattedLogs = [];
    foreach ($logs as $i => $log) {
        $log['operation'] = '<a href="'.url('System', 'SystemLog', 'log', ['year' => $year, 'month' => $month, 'day' => $day, 'hash' => $i]) . '" target="Blank">查看</a>';
        $log['message'] = Str::limit($log['message'], 50);

        $formattedLogs[] = (object)$log;
    }

    $uiGrid->setData($formattedLogs);

    $uiGrid->setFields(
        [
            'name' => 'type',
            'label' => '类型',
            'align' => 'center',
        ],
        [
            'name' => 'file',
            'label' => '文件',
            'align' => 'left'
        ],
        [
            'name' => 'line',
            'label' => '行号',
            'align' => 'center',
        ],
        [
            'name' => 'message',
            'label' => '错误信息',
            'align' => 'left',
        ],
        [
            'name' => 'create_time',
            'label' => '产生时间',
            'align' => 'left',
        ],
        [
            'name' => 'record_time',
            'label' => '首次产生时间',
            'align' => 'left',
        ],
        [
            'name' => 'operation',
            'label' => '操作',
            'align' => 'left',
        ]
    );

    $uiGrid->setPagination($this->get('pagination'));
    $uiGrid->display();
}
?>
</be-center>