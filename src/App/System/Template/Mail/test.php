<?php
use Be\System\Be;
use Be\System\Request;
?>

<be-head>
<?php
$uiEditor = Be::getUi('editor');
$uiEditor->head();
?>
</be-head>

<be-center>
<?php

$uiEditor = Be::getUi('editor');
$uiEditor->setAction('save', beUrl('System.Mail.test'), '发送');
$uiEditor->setFields(
    array(
        'type'=>'text',
        'name'=>'toEmail',
        'label'=>'收件邮箱',
        'value'=>Request::get('toEmail'),
        'width'=>'200px',
        'validate'=>array(
            'required'=>true,
            'email'=>true
       )
   ),
    array(
        'type'=>'text',
        'name'=>'subject',
        'label'=>'标题',
        'value'=>'系统邮件测试',
        'width'=>'300px',
        'validate'=>array(
            'required'=>true
       )
   ),
    array(
        'type'=>'richtext',
        'name'=>'body',
        'label'=>'内容',
        'value'=>'这是一封测试邮件。',
        'width'=>'500px',
        'height'=>'45px'
   )
);

$uiEditor->display();
?>
</be-center>