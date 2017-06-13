<?php

use Doctrine\DBAL;
use League\CLImate\CLImate;

include_once __DIR__ . '/../vendor/autoload.php';

$base_path = isset($_ENV['PROJECT_PATH']) ? $_ENV['PROJECT_PATH'] : realpath(__DIR__ . '/../');
$app       = new ArrayObject(
    [
        'base_path'          => $base_path,
        'csv_export_path'    => isset($_ENV['DIST_PATH']) ?
            ($_ENV['DIST_PATH'] . '/csv') : ($base_path . '/dist/csv'),
        'showdown_json_path' => $base_path . '/build/showdown',
        'db_file'            => $base_path . '/git_dependencies/veekun-pokedex/pokedex/data/pokedex.sqlite',
    ]
);

$db_config      = [
    'url' => 'sqlite:///' . $app['db_file'],
];
$app['db']      = DBAL\DriverManager::getConnection($db_config, new DBAL\Configuration());
$app['cli']     = new CLImate();
$app['db_exec'] = function ($statement) use ($app) {
    /** @var \Doctrine\DBAL\Connection $db */
    $db = $app['db'];
    /** @var \League\CLImate\CLImate $cli */
    $cli = $app['cli'];
    try {
        $db->beginTransaction();
        $affected_rows = $db->exec($statement);
        $db->commit();

        return $affected_rows;
    } catch (\Exception $exception) {
        $cli->red()->out(' FAILED. Rolling back.');
        $db->rollBack();
        throw $exception;
    } catch (\Throwable $throwable) {
        $cli->red()->out(' FAILED. Rolling back.');
        $db->rollBack();
        throw $throwable;
    } finally {
        $errors = $db->errorInfo();
        if (is_array($errors) && ($errors[0] != PDO::ERR_NONE)) {
            $cli->error('SQL Error:');
            $cli->out(print_r($errors, true));
        }
    }
};

register_shutdown_function(function () use ($app) {
    /** @var \Doctrine\DBAL\Connection $db */
    $db = $app['db'];
    $db->close();
});

$app['load_showdown_json'] = function ($relative_path, $root_element = null) use ($app) {
    $filename = $app['showdown_json_path'] . "/${relative_path}.json";

    $data = (array)json_decode(file_get_contents($filename), true);

    if (!is_null($root_element)) {
        return isset($data[$root_element]) ? $data[$root_element] : [];
    }

    return $data;
};