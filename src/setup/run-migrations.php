<?php

require_once __DIR__ . '/../bootstrap.php';

/** @var \Pokettomonstaa\Database\App $app */
$cli = $app->getCli();

// Backup existing db
@unlink($app->dbFile . '-backup');
@copy($app->dbFile, $app->dbFile . '-backup');

$migration_callables = $app->getMigrations();

foreach ($migration_callables as $migration_name => $callableWithArgs) {
    list($callable, $args) = $callableWithArgs;
    $args[] = $app;

    $affected_rows = $app->runMigration($migration_name, $callable, $args);

    if (is_numeric($affected_rows)) {
        $cli->whisper()->out("Affected rows: ($affected_rows)");
    }
}

$cli->green('MIGRATIONS FINISHED.');
$app->getDb()->close();