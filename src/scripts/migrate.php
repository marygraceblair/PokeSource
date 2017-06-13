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
$migrations_path  = realpath(__DIR__ . '/../migrations');

$raw_sql_migration = function ($sql) use ($app) {
    return $app['db_exec']($sql);
};

$run_migration = function ($migration_name, $callable, array $args = []) use ($app, $migrations_table) {
    /** @var \Doctrine\DBAL\Connection $db */
    $db = $app['db'];
    /** @var \League\CLImate\CLImate $cli */
    $cli = $app['cli'];

    $found_migration = $db
        ->query("SELECT * FROM `${migrations_table}` WHERE name='${migration_name}'")
        ->fetch(PDO::FETCH_ASSOC);

    if (is_array($found_migration) && ($found_migration['name'] == $migration_name)) {
        $cli->whisper()->out("Skipping '${migration_name}' migration...");

        return null;
    }

    $cli->green()->inline("Running '${migration_name}' migration...");

    $result = call_user_func_array($callable, $args);
    $app['db_exec']("INSERT INTO `$migrations_table` (name) VALUES ('$migration_name')");

    return $result;
};

$dir_iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($migrations_path, RecursiveDirectoryIterator::SKIP_DOTS)
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
            $migration_callables[$migration_name][1] = [$sql];
        } else {
            $migration_callables[$migration_name] = [$raw_sql_migration, [$sql]];
        }
    }
}

ksort($migration_callables, SORT_ASC);

foreach ($migration_callables as $migration_name => $callableWithArgs) {
    list($callable, $args) = $callableWithArgs;
    $affected_rows = $run_migration($migration_name, $callable, $args);

    if (is_numeric($affected_rows)) {
        $cli->whisper()->out("Affected rows: ($affected_rows)");
    }
}


$cli->green('MIGRATIONS FINISHED.');
$db->close();