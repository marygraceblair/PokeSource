<?php

require_once __DIR__ . '/../../../bootstrap.php';
require_once __DIR__ . '/../../../../vendor/mevdschee/php-crud-api/api.php';

/** @var \Pokettomonstaa\App\App $app */

$api = new PHP_CRUD_API([
    'dbengine' => 'SQLite',
    'database' => $app->dbFile,
]);
$api->executeCommand();