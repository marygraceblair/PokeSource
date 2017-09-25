<?php

require_once __DIR__ . '/../bootstrap.php';

/** @var \Pokettomonstaa\Database\App $app */
$db = $app->getDb();
$cli = $app->getCli();

// Backup
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

if (!$db->getSchemaManager()->tablesExist([$migrations_table])) {
    $cli->info('Creating migrations table.');
    $app->dbExecTransactional($migrations_create_table_sql);
    sleep(2);
    $db->close();
    $cli->green('DONE.');
}