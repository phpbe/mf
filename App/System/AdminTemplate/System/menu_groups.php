<?php
use Be\System\Be;
?>

<!--{center}-->
<?php
$groups = $this->get('groups');

$uiGrid = Be::getUi('grid');

$uiGrid->setAction('listing', url('System.System.menuGroups'));
$uiGrid->setAction('create', url('System.System.menuGroupEdit'));
$uiGrid->setAction('edit', url('System.System.menuGroupEdit'));
$uiGrid->setAction('delete', url('System.System.menuGroupDelete'));

$uiGrid->setData($groups);

$uiGrid->setFields(
    array(
        'name'=>'name',
        'label'=>'菜单组名',
        'align'=>'left'
    ),
    array(
        'name'=>'className',
        'label'=>'调用类名',
        'align'=>'center',
        'width'=>'180'
    )
);
$uiGrid->display();

?>
<div class="comment">
    <ul>
        <li>* 菜单组类名为开发人员开发时调用。</li>
        <li>* north, south, dashboard 为系统默认顶部菜单,底部和用户中心菜单类名， 禁止改动和删除。</li>
    </ul>
</div>
<!--{/center}-->