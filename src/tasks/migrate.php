<?php

require_once __DIR__ . '/../bootstrap.php';

/** @var \Pokettomonstaa\App\App $app */
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

/**
 * @return \Closure[]
 */
$getMigrations = function () use ($app) {
    $raw_sql_migration = function ($sql) use ($app) {
        return $app->dbExecTransactional($sql);
    };

    $migrations_path = $app->migrationsPath;

    $dir_iterator = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator($migrations_path, \RecursiveDirectoryIterator::SKIP_DOTS)
    );

    $migration_callables = [];

    foreach ($dir_iterator as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) == "php") {
            $migration_name = strtolower(str_replace('.php', '', pathinfo($file, PATHINFO_FILENAME)));

            $migration_callables[$migration_name] = [include $file, []];
        }
    }

    foreach ($dir_iterator as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) == "sql") {
            $migration_name = strtolower(str_replace('.sql', '', pathinfo($file, PATHINFO_FILENAME)));

            $sql = file_get_contents($file);

            if (isset($migration_files[$migration_name])) {
                $migration_callables[$migration_name][] = [$sql];
            } else {
                $migration_callables[$migration_name] = [$raw_sql_migration, [$sql]];
            }
        }
    }

    ksort($migration_callables, SORT_ASC);

    return $migration_callables;
};

/**
 * @param string   $migration_name
 * @param callable $callable
 * @param array    $args
 *
 * @return mixed|null
 */
$runMigration = function ($migration_name, $callable, array $args = []) use ($app) {
    $found_migration = $app->getDb()
        ->query("SELECT * FROM `$app->migrationsTable` WHERE name='${migration_name}'")
        ->fetch(PDO::FETCH_ASSOC);

    if (is_array($found_migration) && ($found_migration['name'] == $migration_name)) {
        $app->getCli()->whisper()->out("Skipping already executed '${migration_name}' migration...");

        return null;
    }

    $app->getCli()->green()->inline("Running '${migration_name}' migration...");

    $result = call_user_func_array($callable, $args);
    $app->dbExecTransactional("INSERT INTO `$app->migrationsTable` (name) VALUES ('$migration_name')");

    return $result;
};

// Execute all migrations from the migrations folder:
$migration_callables = $getMigrations();
foreach ($migration_callables as $migration_name => $callableWithArgs) {
    list($callable, $args) = $callableWithArgs;
    $args[] = $app;

    $affected_rows = $runMigration($migration_name, $callable, $args);

    if (is_numeric($affected_rows)) {
        $cli->whisper()->out("Affected rows: ($affected_rows)");
    }
}

$cli->green('MIGRATIONS FINISHED.');
