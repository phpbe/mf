<?php
$loader = require(__DIR__ . '/vendor/autoload.php');
$loader->addPsr4('Be\\', __DIR__);

$property = Be\System\Be::getProperty('App.System');
$property->path = '/App/System';

$runtime = \Be\System\Be::getRuntime();
$runtime->setRootPath(__DIR__);
$runtime->execute();

