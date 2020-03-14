<?php
$loader = require(__DIR__ . '/vendor/autoload.php');
$loader->addPsr4('Be\\', __DIR__);

$runtime = \Be\System\Be::getRuntime();
$runtime->setRootPath(__DIR__);
$runtime->execute();

