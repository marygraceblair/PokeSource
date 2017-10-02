<?php

require_once __DIR__ . '/../bootstrap.php';

/** @var \Pokettomonstaa\Database\App $app */
$cli = $app->getCli();

// Backup existing db
@unlink($app->dbFile . '-backup');
@copy($app->dbFile, $app->dbFile . '-backup');

$migrations_table = 'db_migrations';

$migrations_create_table_sql = <<<SQL
CREATE TABLE IF NOT EXISTS `${migrations_table}` (
  id            INTEGER PRIMARY KEY AUTOINCREMENT,
  name          VARCHAR(255),
  date_migrated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE (name)
);
SQL;

if (!$app->getDb()->getSchemaManager()->tablesExist([$migrations_table])) {
    $cli->info('Creating migrations table.');
    $app->dbExecTransactional($migrations_create_table_sql);
    sleep(2);
}

// Execute all migrations from the migrations folder:
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