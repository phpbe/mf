<?php
use Be\System\Be;
?>

<!--{head}-->
<?php
$uiGrid = Be::getUi('grid');
$uiGrid->head();
?>
<!--{/head}-->

<!--{center}-->
<?php
$users = $this->users;
$roles = $this->roles;

$roleMap = array('0'=>'所有角色');
foreach ($roles as $role) {
    $roleMap[$role->id] = $role->name;
}

$uiGrid = Be::getUi('grid');

$uiGrid->setAction('list', url('System.AdminUser.users'));
$uiGrid->setAction('create', url('System.AdminUser.edit'));
$uiGrid->setAction('edit', url('System.AdminUser.edit'));
$uiGrid->setAction('unblock', url('System.AdminUser.unblock'), '启用');
$uiGrid->setAction('block', url('System.AdminUser.block'));
$uiGrid->setAction('delete', url('System.AdminUser.delete'));

$uiGrid->setFilters(
    array(
        'type'=>'text',
        'name'=>'key',
        'label'=>'关键字',
        'value'=>$this->get('key'),
        'width'=>'120px'
   ),
    array(
        'type'=>'select',
        'name'=>'status',
        'label'=>'状态',
        'options'=>array(
            '-1'=>'所有',
            '0'=>'公开',
            '1'=>'屏蔽'
       ),
        'value'=>$this->get('status'),
        'width'=>'80px'
   ),
    array(
        'type'=>'select',
        'name'=>'roleId',
        'options'=>$roleMap,
        'value'=>$this->get('roleId'),
        'width'=>'160px'
   )
);

$configAdminUser = Be::getConfig('System.AdminUser');

foreach ($users as $user) {
    $user->register_time =	date('Y-m-d H:i',$user->register_time);
    $user->last_login_time = date('Y-m-d H:i',$user->last_login_time);
    $user->avatar = '<img src="'.Be::getRuntime()->getDataUrl().'/System/AdminUser/avatar/'.($user->avatar_s == ''?('default/'.$configAdminUser->defaultAvatarS):$user->avatarS).'" width="32" />';
    $user->role_name = '<span class="label label-info">'.$roleMap[$user->roleId].'</span>';
}

$uiGrid->setData($users);
$uiGrid->setFields(
    array(
        'name'=>'id',
        'label'=>'ID',
        'align'=>'center',
        'width'=>'30',
        'orderBy'=>'id'
    ),
    array(
        'name'=>'avatar',
        'label'=>'头像',
        'align'=>'center',
        'style'=>'margin:0;padding:2px;',
        'width'=>'50'
    ),
    array(
        'name'=>'username',
        'label'=>'用户名',
        'align'=>'left',
        'orderBy'=>'username'
    ),
    array(
        'name'=>'name',
        'label'=>'名称',
        'align'=>'left',
        'width'=>'80'
    ),
    array(
        'name'=>'email',
        'label'=>'邮箱',
        'align'=>'center',
        'width'=>'200',
        'orderBy'=>'email'
    ),
    array(
        'name'=>'register_time',
        'label'=>'注册时间',
        'align'=>'center',
        'width'=>'120',
        'orderBy'=>'register_time'
    ),
    array(
        'name'=>'last_login_time',
        'label'=>'上次登陆时间',
        'align'=>'center',
        'width'=>'120',
        'orderBy'=>'last_login_time'
    ),
    array(
        'name'=>'role_name',
        'label'=>'',
        'align'=>'center',
        'width'=>'80'
    )
);


$uiGrid->setPagination($this->pagination);
$uiGrid->orderBy($this->orderBy, $this->orderByDir);
$uiGrid->display();
?>
<!--{/center}-->
