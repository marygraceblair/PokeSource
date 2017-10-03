<?php

require_once __DIR__ . '/../bootstrap.php';

/** @var \Pokettomonstaa\App\App $app */
$cli = $app->getCli();

$options = [
    'include_pokemon'       => true,
    'include_pokemon.shiny' => true,
    'include_pokemon.right' => true,
    'include_items'         => true,
    'include_symbols'       => true,
];

$cli->out('Creating sprite sheet...');

// TODO: implement sprites.php

$cli->lightBlue('Oh wait, this still needs to be implemented.');
