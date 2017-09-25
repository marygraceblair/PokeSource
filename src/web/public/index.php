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

$getIconUrl = function ($name, $folder = '/regular') use ($app) {
    $file = $app->publicPath . "/assets/pokesprite/icons/pokemon{$folder}/{$name}.png";

    if (!file_exists($file)) {
        $name = "unknown";
        $folder = "";
    }

    return "{$app->baseUrl}/assets/pokesprite/icons/pokemon{$folder}/{$name}.png";
};

$icons = [];

foreach ($pokemon_species['pokemon_species'] as $species) {
    $species_id = $species['id'];
    $species_name = $species['identifier'];

    $icons[$species_id] = [
        'id'        => $species_id,
        'name'      => $species_name,
        'api_url'   => $app->buildApiUrl('/zz_pokemon/' . $species_id, ['transform' => 1]),
        'icon_url'  => $getIconUrl($species_name),
        'forms'     => [],
        'form_name' => null,
    ];

    foreach ($species['pokemon'] as $pkm) {
        $pokemon_name = $pkm['identifier'];

        if ($pokemon_name != $species_name) {
            $icons[$species_id]['forms'][$pokemon_name] = [
                'name'       => $pokemon_name,
                'icon_url'   => $getIconUrl($pokemon_name),
                'is_default' => $pkm['is_default'],
            ];

            if ($pkm['is_default'] && !$icons[$species_id]['form_name']) {
                $icons[$species_id]['form_name'] = $pokemon_name;
                unset($icons[$species_id]['forms'][$pokemon_name]);
            }
        }

        foreach ($pkm['pokemon_forms'] as $form) {
            $form_name = $form['identifier'];

            if (($form_name != $species_name) && ($form_name != $pokemon_name)) {
                $icons[$species_id]['forms'][$form_name] = [
                    'name'       => $form_name,
                    'icon_url'   => $getIconUrl($form_name),
                    'is_default' => $pkm['is_default'],
                ];

                if ($form['is_default'] && !$icons[$species_id]['form_name']) {
                    $icons[$species_id]['form_name'] = $form_name;
                    unset($icons[$species_id]['forms'][$form_name]);
                }
            }
        }
    }
}

// header('Content-Type: application/json');
// echo json_encode(array_values($icons));

echo $app->renderTemplate('icons-list.html', ['icons' => $icons]);
