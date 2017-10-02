<?php

/** @var \Pokettomonstaa\App\App $app */
$app = $this;
$db = $app->getDb();

// Export CSV files
$app->assureDir($app->distPath . '/csv');
$app->exportDbToCsv();
$app->getCli()->green("DONE!");

// Stop locking DB file
$db->close();