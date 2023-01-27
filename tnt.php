<?php
require "config.php";
require __DIR__ . '/vendor/autoload.php';

use TeamTNT\TNTSearch\TNTSearch;

$tnt = new TNTSearch;

$tnt->loadConfig($tnt_config);

$indexer = $tnt->createIndex('title.index');
$indexer->query('SELECT id, title FROM videos;');
//$indexer->setLanguage('german');
$indexer->run();
