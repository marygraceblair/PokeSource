<?php

/** @var \Pokettomonstaa\App\App $app */
$app = $this;
$db = $app->getDb();

$export_path = $app->assureDir($app->distPath . '/data/csv');

$tables = $db->getSchemaManager()->listTables();

$app->getCli()->lightBlue("Creating CSV data files...");

foreach ($tables as $table) {
    $tableName = $table->getName();

    if (
        in_array($tableName, ['db_migrations'])
        | preg_match('/^(sys\\/|sqlite_).*/', $tableName)
    ) {
        continue;
    }

    $export_file = $export_path . "/{$tableName}.csv";
    # @unlink($export_file);

    $output = '';
    $app->getCli()->out(" > " . str_replace($app->distPath, 'dist', $export_file));

    if ($table->hasPrimaryKey()) {
        $orderBy = "ORDER BY 1 ASC";
    } else {
        $orderBy = "ORDER BY 1 ASC, 2 ASC";
    }

    exec(
        'sql2csv' .
        ' --db "sqlite:///' . $app->dbFile . '"' .
        ' --query "SELECT * FROM \\`' . $tableName . '\\` ' . $orderBy . '"' .
        " > ${export_file}",
        $output
    );

    $output = (array)$output;
    foreach ($output as $line) {
        $app->getCli()->out($line);
    }
}

$app->getCli()->green("DONE!");
