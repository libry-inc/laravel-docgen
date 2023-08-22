<?php

namespace Libry\LaravelDocgen\Collector\Db;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

class TableCollection extends Collection
{
    public function __construct(public readonly string $database, Arrayable|iterable $tables)
    {
        parent::__construct($tables);
    }
}
