<?php

require_once __DIR__ . '/../bootstrap.php';

/** @var \Pokettomonstaa\Database\App $app */

$dirIterator = new \RecursiveIteratorIterator(
    new \RecursiveDirectoryIterator(__DIR__ . '/exporters', \RecursiveDirectoryIterator::SKIP_DOTS)
);

// Run all exporter scripts
foreach ($dirIterator as $filepath) {
    $ext = pathinfo($filepath, PATHINFO_EXTENSION);
    $filename = pathinfo($filepath, PATHINFO_FILENAME);
    if (
        (pathinfo($filepath, PATHINFO_EXTENSION) == "php")
        && (preg_match('/^export-.*/', $filename))
    ) {
        switch ($ext) {
            case "php":
                $app->execFile($filepath);
                break;
            case "sh":
                $app->execCmd($filepath);
                break;
            default:
                throw new \Exception('Cannot execute exporter ' . $filepath);
        }
    }
}

// Creates DB bundle:
$app->getCli()->lightBlue('Creating DB bundle...');
$export_path = $app->assureDir($app->distPath . '/db');
$cmd = 'zip -j "' . $export_path . DIRECTORY_SEPARATOR . 'pokemon.sqlite.zip" ' .
    ' "' . $app->dbFile . '" ';

$app->execCmd($cmd);
$app->getCli()->green('DONE!');