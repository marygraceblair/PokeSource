<?php

require_once __DIR__ . '/../app.php';

/** @var \Doctrine\DBAL\Connection $db */
$db = $app['db'];
/** @var \League\CLImate\CLImate $cli */
$cli = $app['cli'];

// Backup
@unlink($app['db_file'] . '-backup');
@copy($app['db_file'], $app['db_file'] . '-backup');

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
    $app['db_exec']($migrations_create_table_sql);
    sleep(2);
    $db->close();
}

$cli->green('FINISHED.');