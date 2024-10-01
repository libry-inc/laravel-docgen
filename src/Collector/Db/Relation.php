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
        $this->localColumns = $localColumns ??= [Str::singular($foreignTable).'_id'];
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
}
