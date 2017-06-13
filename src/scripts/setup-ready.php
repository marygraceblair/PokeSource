<?php

require_once __DIR__ . '/../app.php';

/** @var \Doctrine\DBAL\Connection $db */
$db = $app['db'];
/** @var \League\CLImate\CLImate $cli */
$cli = $app['cli'];

$dependencies_path = $app['base_path'] . '/git_dependencies';

if (
    !is_dir($dependencies_path . '/veekun-pokedex/pokedex')
    || !is_dir($dependencies_path . '/pokemon-showdown/data')
    || !is_dir($app['base_path'] . '/vendor/doctrine')
) {
    exit(1);
}

if (!$db->getSchemaManager()->tablesExist(['db_migrations'])) {
    exit(1);
}

exit(0);