<?php

require_once __DIR__ . '/../bootstrap.php';

/** @var \Pokettomonstaa\Database\App $app */
$cli = $app->getCli();

$export_path = $app->distPath . '/db';

if (!realpath($export_path) || !is_dir($export_path)) {
    mkdir($export_path, 0755, true);
}

$output = '';

exec(
    'zip' .
    ' -j "' . $export_path . DIRECTORY_SEPARATOR . 'pokemon.sqlite.zip" ' .
    ' "' . $app->dbFile . '" ' .
    $output
);

$output = (array)$output;
foreach ($output as $line) {
    $cli->out($line);
}