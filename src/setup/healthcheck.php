<?php

require_once __DIR__ . '/../bootstrap.php';

// This script checks if the setup has finished or not.

/** @var \Pokettomonstaa\Database\App $app */
$db = $app->getDb();

$dependencies_path = $app->vendorPath;

if (
    !is_dir($dependencies_path . '/veekun-pokedex/pokedex')
    || !is_dir($dependencies_path . '/pokemon-showdown/data')
    || !is_dir($dependencies_path . '/doctrine')
) {
    exit(1);
}

if (!$app->getDb()->getSchemaManager()->tablesExist(['db_migrations'])) {
    exit(1);
}

exit(0);