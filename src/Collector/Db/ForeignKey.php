<?php

namespace Libry\LaravelDocgen\Collector\Db;

class ForeignKey
{
    public readonly string $name;

    public readonly array $localColumns;

    public readonly string $foreignSchema;

    public readonly string $foreignTable;

    public readonly array $foreignColumns;

    public readonly string $onUpdate;

    public readonly string $onDelete;

    final public function __construct(array $raw)
    {
        $this->name = $raw['name'];
        $this->localColumns = $raw['columns'];
        $this->foreignSchema = $raw['foreign_schema'];
        $this->foreignTable = $raw['foreign_table'];
        $this->foreignColumns = $raw['foreign_columns'];
        $this->onUpdate = $raw['on_update'];
        $this->onDelete = $raw['on_delete'];
    }
}
