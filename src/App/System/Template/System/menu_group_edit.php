<?php
use Be\System\Be;
?>

<be-head>
<?php
$uiEditor = Be::getUi('editor');
$uiEditor->head();
?>
</be-head>

<be-center>
<?php
$menuGroup = $this->get('menuGroup');

$uiEditor = Be::getUi('editor');


$uiEditor->setAction('save', url('System.System.menuGroupEditSave'));	// 显示提交按钮
$uiEditor->setAction('reset');// 显示重设按钮
$uiEditor->setAction('back', url('System.System.menuGroups'));	// 显示返回按钮

if ($menuGroup->className == 'north' || $menuGroup->className == 'south' || $menuGroup->className == 'dashboard') {
    echo '<script type="text/javascript" language="javascript">$(function(){ $("#className").prop("disabled", true); });</script>';
}

$uiEditor->setFields(
    array(
        'type'=>'text',
        'name'=>'name',
        'label'=>'菜单组名',
        'value'=>$menuGroup->name,
        'width'=>'200px',
        'validate'=>array(
            'required'=>true,
            'maxLength'=>60
        )
    ),
    array(
        'type'=>'text',
        'name'=>'className',
        'label'=>'调用类名',
        'value'=>$menuGroup->className,
        'width'=>'120px',
        'validate'=>array(
            'required'=>true,
            'maxLength'=>60
        )
    )
);

$uiEditor->addHidden('id', $menuGroup->id);
$uiEditor->display();
?>
</be-center>