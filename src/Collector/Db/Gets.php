<?php

namespace Libry\LaravelDocgen\Collector\Db;

trait Gets
{
    public function __get(string $name): mixed
    {
        $method = 'get'.ucfirst($name);

        if (!method_exists($this, $method)) {
            throw new \LogicException(static::class.'::'.$name.' is not implemented.');
        }

        return $this->{$method}();
    }
}
