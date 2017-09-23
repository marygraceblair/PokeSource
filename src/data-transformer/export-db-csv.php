<?php

require_once __DIR__ . '/../bootstrap.php';

/** @var \Pokettomonstaa\Database\App $app */
$app->exportDbToCsv();
$app->getDb()->close();