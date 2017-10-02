<?php

namespace Pokettomonstaa\App\Dtos;

class PokemonDto extends ResourceDto
{
    /**
     * @var bool
     */
    public $is_default;
    /**
     * @var string
     */
    public $form_name;
    /**
     * @var string
     */
    public $icon_url;
    /**
     * @var PokemonDto[]
     */
    public $forms = [];
}