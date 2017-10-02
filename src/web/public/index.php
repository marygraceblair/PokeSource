<?php

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../../vendor/mevdschee/php-crud-api/api.php';

/** @var \Pokettomonstaa\Database\App $app */

$pokemon_species = $app->sendApiRequest('/pokemon_species', [
    'include'   => [
        'pokemon',
        'pokemon_types',
        'pokemon_stats',
        'pokemon_abilities',
        'pokemon_forms',
    ],
    'transform' => 1,
]);

$items = $app->sendApiRequest('/items', [
    'transform' => 1,
]);

$getPokemonIconUrl = function ($name, $folder = '/regular') use ($app) {
    $file = $app->buildPath . "/pokesprite/pokemon{$folder}/{$name}.png";

    if (!file_exists($file)) {
        $name = "unknown";
        $folder = "";
    }

    return "{$app->baseUrl}/assets/pokesprite/pokemon{$folder}/{$name}.png";
};

$getItemIconUrl = function ($name) use ($app) {
    $folder = "items";
    $file = $app->buildPath . "/pokesprite/items/{$name}.png";

    if (!file_exists($file)) {
        $name = "unknown-item";
        $folder = "other";
    }

    return "{$app->baseUrl}/assets/pokesprite/{$folder}/{$name}.png";
};

$pokemonIcons = [];

foreach ($pokemon_species['pokemon_species'] as $species) {
    $species_id = $species['id'];
    $species_name = $species['identifier'];

    $pokemonIcons[$species_id] = [
        'id'        => $species_id,
        'name'      => $species_name,
        'api_url'   => $app->buildApiUrl('/zz_pokemon/' . $species_id, ['transform' => 1]),
        'icon_url'  => $getPokemonIconUrl($species_name),
        'forms'     => [],
        'form_name' => null,
    ];

    foreach ($species['pokemon'] as $pkm) {
        $pokemon_name = $pkm['identifier'];

        if ($pokemon_name != $species_name) {
            $pokemonIcons[$species_id]['forms'][$pokemon_name] = [
                'name'       => $pokemon_name,
                'icon_url'   => $getPokemonIconUrl($pokemon_name),
                'is_default' => $pkm['is_default'],
            ];

            if ($pkm['is_default'] && !$pokemonIcons[$species_id]['form_name']) {
                $pokemonIcons[$species_id]['form_name'] = $pokemon_name;
                unset($pokemonIcons[$species_id]['forms'][$pokemon_name]);
            }
        }

        foreach ($pkm['pokemon_forms'] as $form) {
            $form_name = $form['identifier'];

            if (($form_name != $species_name) && ($form_name != $pokemon_name)) {
                $pokemonIcons[$species_id]['forms'][$form_name] = [
                    'name'       => $form_name,
                    'icon_url'   => $getPokemonIconUrl($form_name),
                    'is_default' => $pkm['is_default'],
                ];

                if ($form['is_default'] && !$pokemonIcons[$species_id]['form_name']) {
                    $pokemonIcons[$species_id]['form_name'] = $form_name;
                    unset($pokemonIcons[$species_id]['forms'][$form_name]);
                }
            }
        }
    }
}


$itemsIcons = [];
foreach ($items['items'] as $item) {
    $item_id = $item['id'];
    $item_name = $item['identifier'];

    $itemsIcons[$item_id] = [
        'id'        => $item_id,
        'name'      => $item_name,
        'api_url'   => $app->buildApiUrl('/items/' . $item_id, ['transform' => 1]),
        'icon_url'  => $getItemIconUrl($item_name),
        'forms'     => [],
        'form_name' => null,
    ];
}

// header('Content-Type: application/json');
// echo json_encode(array_values($icons));

echo $app->renderTemplate(
    'icons-list.html',
    ['icons' => array_merge(array_values($pokemonIcons), array_values($itemsIcons))]
);
