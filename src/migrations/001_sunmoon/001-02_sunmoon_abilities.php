<?php

use Illuminate\Support\Fluent;

error_reporting(-1);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../app.php';

return function () use ($app) {
    /** @var \Doctrine\DBAL\Connection $db */
    $db = $app['db'];
    /** @var \League\CLImate\CLImate $cli */
    $cli = $app['cli'];

    $abilities = $app['load_showdown_json']('data/abilities', 'BattleAbilities');
    $queries   = [];

    foreach ($abilities as $identifier => $ability) {
        /** @var mixed|Fluent $ability */
        $ability = new Fluent($ability);

        if (($ability->isNonStandard) || ($ability->num < 192) || ($ability->num > 232)) {
            continue;
        }

        $name       = $db->quote($ability->name . '', 'string');
        $identifier = $db->quote($identifier . '', 'string');
        $shortDesc  = $db->quote($ability->shortDesc . '', 'string');
        $desc       = $db->quote($ability->desc . '', 'string');

        $queries[] = "INSERT INTO abilities (id, identifier, generation_id, is_main_series) " .
                     "VALUES ({$ability->num}, {$identifier}, 7, 1)";
        $queries[] = "INSERT INTO ability_names (ability_id, local_language_id, name) " .
                     "VALUES ({$ability->num}, 9, {$name})";
        $queries[] = "INSERT INTO ability_prose (ability_id, local_language_id, short_effect, effect) " .
                     "VALUES ({$ability->num}, 9, {$shortDesc}, {$desc})";
    }

    $affected_rows = count($queries);

    if ($affected_rows > 0) {
        $db->exec(implode(';', $queries));
    }

    print_r($db->errorInfo());

    return $affected_rows;
};