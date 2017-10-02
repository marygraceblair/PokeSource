<?php

require_once __DIR__ . '/../../bootstrap.php';

/** @var \Pokettomonstaa\App\App $app */

$pokeApi = new \Pokettomonstaa\App\Repo($app);

echo $app->renderTemplate(
    'icons-list.html',
    [
        'pokemon_icons' => $pokeApi->getPokemon(),
        'item_icons'    => $pokeApi->getItems(),
    ]
);
