<?php

require_once __DIR__ . '/../app.php';

/** @var \Doctrine\DBAL\Connection $db */
$db = $app['db'];
/** @var \League\CLImate\CLImate $cli */
$cli = $app['cli'];

$export_path = $app['csv_export_path'];
if (!realpath($export_path) || !is_dir($export_path)) {
    mkdir($export_path, 0755, true);
}

$tables = $db->getSchemaManager()->listTables();
foreach ($tables as $table) {
    $export_file = $export_path . "/{$table->getName()}.csv";
    # @unlink($export_file);

    $output = '';
    $cli->out("Exporting {$export_file}");

    if ($table->hasPrimaryKey()) {
        $orderBy = "ORDER BY 1 ASC";
    } else {
        $orderBy = "ORDER BY 1 ASC, 2 ASC";
    }

    exec(
        'sql2csv' .
        ' --db "sqlite:///' . $app['db_file'] . '"' .
        ' --query "SELECT * FROM \\`' . $table->getName() . '\\` ' . $orderBy . '"' .
        " > ${export_file}",
        $output
    );

    $output = (array)$output;
    foreach ($output as $line) {
        $cli->out($line);
    }
}
$db->close();