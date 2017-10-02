<?php

return function (\Pokettomonstaa\App\App $app) {
    $db = $app->getDb();
    $tables = $db->getSchemaManager()->listTables();

    // Drop all tables that are not durable for future generations and/or are not main series related
    foreach ($tables as $table) {
        if (
        preg_match('/^(conquest|contest|super_contest|pokeathlon|pal_park)/',
            $table->getName()
        )
        ) {
            $db->getSchemaManager()->dropTable($table->getName());
        }
    }

    return 0; // affected rows
};