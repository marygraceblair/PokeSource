<?php

namespace Pokettomonstaa\App;

use Pokettomonstaa\App\Dtos\ItemDto;
use Pokettomonstaa\App\Dtos\PokemonDto;

class Repo
{
    /**
     * @var App
     */
    private $app;

    /**
     *
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * @param string $name
     * @param string $folder
     *
     * @return string
     */
    public function getPokemonIconUrl($name, $folder = '/regular')
    {
        $folder = '/pokemon' . $folder;
        $file = $this->app->distPath . "/assets/img/icons/{$folder}/{$name}.png";

        if (!file_exists($file)) {
            $name = "unknown";
            $folder = "other";
        }

        return "{$this->app->baseUrl}/assets/img/icons/{$folder}/{$name}.png";
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function getItemIconUrl($name)
    {
        $folder = "items";
        $file = $this->app->distPath . "/assets/img/icons/items/{$name}.png";

        if (!file_exists($file)) {
            $name = "unknown";
            $folder = "other";
        }

        return "{$this->app->baseUrl}/assets/img/icons/{$folder}/{$name}.png";
    }

    /**
     * @return PokemonDto[]
     */
    public function getPokemon()
    {
        $pokemon_species = $this->app->sendApiRequest('/pokemon_species', [
            'include'   => [
                'pokemon',
                //'pokemon_types',
                //'pokemon_stats',
                //'pokemon_abilities',
                'pokemon_forms',
            ],
            'transform' => 1,
        ]);

        $dtos = [];

        foreach ($pokemon_species['pokemon_species'] as $species) {
            $species_id = $species['id'];
            $species_name = $species['identifier'];

            $dtos[$species_id] = new PokemonDto([
                'id'           => $species_id,
                'name'         => $species_name,
                'resource_url' => $this->app->buildApiUrl('/zz_pokemon/' . $species_id, ['transform' => 1]),
                'icon_url'     => $this->getPokemonIconUrl($species_name),
                'forms'        => [],
                'form_name'    => null,
            ]);

            foreach ($species['pokemon'] as $pkm) {
                $pokemon_name = $pkm['identifier'];

                if ($pokemon_name != $species_name) {
                    $dtos[$species_id]->forms[$pokemon_name] = new PokemonDto([
                        'name'       => $pokemon_name,
                        'icon_url'   => $this->getPokemonIconUrl($pokemon_name),
                        'is_default' => (bool)$pkm['is_default'],
                    ]);

                    if ($pkm['is_default'] && !$dtos[$species_id]['form_name']) {
                        $dtos[$species_id]['form_name'] = $pokemon_name;
                        //unset($dtos[$species_id]->forms[$pokemon_name]);
                    }
                }

                foreach ($pkm['pokemon_forms'] as $form) {
                    $form_name = $form['identifier'];

                    if (($form_name != $species_name) && ($form_name != $pokemon_name)) {
                        $dtos[$species_id]->forms[$form_name] = new PokemonDto([
                            'name'       => $form_name,
                            'icon_url'   => $this->getPokemonIconUrl($form_name),
                            'is_default' => (bool)$pkm['is_default'],
                        ]);

                        if ($form['is_default'] && !$dtos[$species_id]['form_name']) {
                            $dtos[$species_id]['form_name'] = $form_name;
                            //unset($dtos[$species_id]->forms[$form_name]);
                        }
                    }
                }
            }
        }

        return $dtos;
    }

    /**
     * @return ItemDto[]
     */
    public function getItems()
    {
        $items = $this->app->sendApiRequest('/items', [
            'transform' => 1,
        ]);

        $dtos = [];

        foreach ($items['items'] as $item) {
            $item_id = $item['id'];
            $item_name = $item['identifier'];

            $dtos[$item_id] = new ItemDto([
                'id'           => $item_id,
                'name'         => $item_name,
                'resource_url' => $this->app->buildApiUrl('/items/' . $item_id, ['transform' => 1]),
                'icon_url'     => $this->getItemIconUrl($item_name),
            ]);
        }

        return $dtos;
    }
}
