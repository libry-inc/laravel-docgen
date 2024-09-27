<?php

namespace Libry\LaravelDocgen\Collector\Db;

class Index
{
    public readonly string $name;

    public readonly array $columns;

    public readonly string $type;

    public readonly bool $unique;

    public readonly bool $primary;

    public function __construct(array $raw)
    {
        $this->name = $raw['name'];
        $this->columns = $raw['columns'];
        $this->type = $raw['type'];
        $this->unique = $raw['unique'];
        $this->primary = $raw['primary'];
    }
}
