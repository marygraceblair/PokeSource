<?php

namespace Pokettomonstaa\App\Dtos;

class ResourceDto extends AbstractDto
{
    /**
     * @var int
     */
    public $id;
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $resource_url;
}