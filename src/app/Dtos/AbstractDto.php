<?php

namespace Pokettomonstaa\App\Dtos;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class AbstractDto implements Arrayable, Jsonable, \ArrayAccess
{
    public function __construct(array $properties = [])
    {
        foreach ($properties as $k => $v) {
            $this->$k = $v;
        }
    }

    final public function __get($name)
    {
        throw new \Exception($this->getUndefinedErrorMessage("\${$name} property"));
    }

    final public function __set($name, $value)
    {
        throw new \Exception($this->getUndefinedErrorMessage("\${$name} property"));
    }

    final public function __call($name, $arguments)
    {
        throw new \Exception($this->getUndefinedErrorMessage("{$name}() method"));
    }

    private function getUndefinedErrorMessage($name)
    {
        return static::class . "::{$name} is not defined. Dynamic access is disabled for DTOs.";
    }

    public function toArray()
    {
        return (array)$this;
    }

    public function toJson($options = 0)
    {
        return \GuzzleHttp\json_encode($this->toArray(), $options);
    }

    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }

    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->$offset);
    }


}