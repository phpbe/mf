<?php
use Be\System\Be;
?>

<be-head>
<?php
$uiGrid = Be::getUi('grid');
$uiGrid->head();
?>
<link type="text/css" rel="stylesheet" href="bootstrap/2.3.2/css/bootstrap-lightbox.css" />
<script type="text/javascript" language="javascript" src="bootstrap/2.3.2/js/bootstrap-lightbox.js"></script>

<link type="text/css" rel="stylesheet" href="<?php echo Be::getProperty('App.System')->getUrl(); ?>/AdminTemplate/System/css/themes.css">
<script type="text/javascript" language="javascript" src="<?php echo Be::getProperty('App.System')->getUrl(); ?>/AdminTemplate/System/js/themes.js"></script>
</be-head>

<be-center>
<?php
$themes = $this->get('themes');

$uiGrid = Be::getUi('grid');

$uiGrid->setAction('listing', beUrl('System.System.themes'));
$uiGrid->setAction('create', beUrl('System.System.remoteThemes'), '安装新主题');


$configSystem = Be::getConfig('System.System');
foreach ($themes as $key=>$theme) {
    $theme->key = $key;

    $theme->isDefault = $configSystem->theme == $key?1:0;

    if ($theme->authorWebsite) {
        $theme->authorWebsite = '<a href="'.$theme->authorWebsite.'" target="Blank" class="muted">'.$theme->authorWebsite.'</a>';
    } else {
        $theme->authorWebsite = '';
    }

    $theme->deleteHtml = '<a class="icon delete"'.($theme->isDefault?' style="display:none;"':'').' href="javascript:;" onclick="javascript:deleteTheme(this, \''.$key.'\');"></a>';
}

$uiGrid->setData($themes);

$uiGrid->setFooter('共安装了 <strong>'.count($themes).'</strong> 个主题');


$thumbnailTemplate = '';
$thumbnailTemplate .= '<a href="javascript:" onclick="javascript:jQuery(\'#themeThumbnail_{id}\').lightbox();" data-title="" data-content="<div style=\'width:400px;height:400px;line-height:400px;text-align:center;\'><img src=\''.beUrl().'/themes/{key}/{thumbnailM}\' style=\'max-width:400px;\' /></div>" data-toggle="popover" data-html="true" data-trigger="hover">';
$thumbnailTemplate .= '	<img src="'.beUrl().'/themes/{key}/{thumbnailS}" style="max-width:120px;" border="0" />';
$thumbnailTemplate .= '</a>';
$thumbnailTemplate .= '<div class="lightbox fade hide" id="themeThumbnail_{id}">';
$thumbnailTemplate .= '	<div class="lightbox-content">';
$thumbnailTemplate .= '		<img src="'.beUrl().'/themes/{key}/{thumbnailL}" />';
$thumbnailTemplate .= '		<div class="lightbox-caption"><p>{name}</p></div>';
$thumbnailTemplate .= '	</div>';
$thumbnailTemplate .= '</div>';



$labelTemplagte = '<strong>{name}</strong>';
$labelTemplagte .= '<div class="muted">{description}</div>';

$authorTemplate = '<strong>{author}</strong><br />';
$authorTemplate .= '{authorEmail}<br />';
$authorTemplate .= '{authorWebsite}';


$uiGrid->setFields(
    array(
        'name'=>'thumbnail',
        'label'=>'缩略图',
        'align'=>'center',
        'template'=>$thumbnailTemplate,
        'width'=>'130'
    ),
    array(
        'name'=>'label',
        'label'=>'名称/详细描述',
        'align'=>'left',
        'template'=>$labelTemplagte
    ),
    array(
        'name'=>'author',
        'label'=>'作者',
        'align'=>'left',
        'template'=>$authorTemplate,
        'width'=>'200'
    ),
    array(
        'label'=>'设为默认主题',
        'align'=>'center',
        'width'=>'120',
        'template'=>'<a href="javascript:;" onclick="javascript:setDefault(\'{key}\')" class="default default-{isDefault}" id="default-{key}"></a>'
    ),
    array(
        'name'=>'deleteHtml',
        'label'=>'',
        'align'=>'center',
        'width'=>'120'
    )
);
$uiGrid->display();
?>
</be-center>