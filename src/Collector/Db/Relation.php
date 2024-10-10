<?php

namespace Libry\LaravelDocgen\Collector\Db;

use Illuminate\Support\Str;

class Relation
{
    public readonly array $localColumns;

    final public function __construct(
        public readonly string $name,
        public readonly string $foreignTable,
        public readonly array $foreignColumns = ['id'],
        ?array $localColumns = null,
        public readonly string $line = '--',
        public readonly ?string $comment = null,
    ) {
        $this->localColumns = $localColumns ??= [static::guessBelongsToKey($foreignTable)];
    }

    public static function create(
        string $foreignTable,
        string $line = '--',
        ?string $comment = null,
        ?string $name = null,
        array $foreignColumns = ['id'],
        ?array $localColumns = null,
    ): static {
        $localColumns ??= [static::guessBelongsToKey($foreignTable)];

        return new static(
            $name ?? implode('_', $localColumns).'_pseudo_relation',
            $foreignTable,
            $foreignColumns,
            $localColumns,
            $line,
            $comment
        );
    }

    public static function fromForeignKey(ForeignKey $foreignKey, array $options = []): static
    {
        return new static(
            $options['name'] ?? $foreignKey->name,
            $options['foreignTable'] ?? $foreignKey->foreignTable,
            $options['foreignColumns'] ?? $foreignKey->foreignColumns,
            $options['localColumns'] ?? $foreignKey->localColumns,
            $options['line'] ?? '--',
            $options['comment'] ?? null,
        );
    }

    protected static function guessBelongsToKey(string $foreignTable): string
    {
        return Str::singular($foreignTable).'_id';
    }
}
