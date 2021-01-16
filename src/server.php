<?php
require(dirname(__DIR__) . '/vendor/autoload.php');

$runtime = new \Be\Mf\Runtime\Driver();
$runtime->setRootPath(__DIR__);
$runtime->setCacheDir('Cache');
$runtime->setDataDir('Data');
\Be\F\Runtime\RuntimeFactory::setInstance($runtime);
$runtime->execute();
