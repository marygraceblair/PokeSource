<?php

require_once __DIR__ . '/../bootstrap.php';

/** @var \Pokettomonstaa\Database\App $app */
$db = $app->getDb();

// Export CSV files
$app->assureDir($app->distPath . '/csv');
$app->exportDbToCsv();
$app->getCli()->green("DONE!");

// Stop locking DB file
$db->close();