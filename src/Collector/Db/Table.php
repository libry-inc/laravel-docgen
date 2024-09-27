<?php

namespace Libry\LaravelDocgen\Collector\Db;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder as SchemaBuilder;

/**
 * @property string $logicalName
 * @property string $description
 * @property ?Index $primary
 */
class Table
{
    use Gets;

    public readonly string $name;

    public readonly string $comment;

    /** @var Column[] key: a column name */
    public readonly array $columnMap;

    /** @var Index[] key: an index name */
    public readonly array $indexMap;

    /** @var ForeignKey[] key: a foreign key name */
    public readonly array $foreignKeyMap;

    public function __construct(protected readonly Connection $connection, protected readonly array $raw)
    {
        $builder = $connection->getSchemaBuilder();
        $this->name = $raw['name'];
        $this->comment = $raw['comment'] ?? '';
        $this->indexMap = self::createIndexMap($builder);
        $this->foreignKeyMap = self::createForeignKeyMap($builder);
        $this->columnMap = self::createColumnMap($builder);
    }

    /**
     * Get the logical name of this table
     * - treat the 1st row of this table comment as a logical name.
     */
    protected function getLogicalName(): string
    {
        return $this->explodeComment(0);
    }

    /**
     * Get the description of this table
     * - treat the 2nd and after rows of this table comment as a description.
     */
    protected function getDescription(): string
    {
        return $this->explodeComment(1);
    }

    protected function getPrimary(): ?Index
    {
        foreach ($this->indexMap as $index) {
            if ($index->primary) {
                return $index;
            }
        }

        return null;
    }

    public function iterateExplicitIndexes(): \Generator
    {
        foreach ($this->indexMap as $name => $index) {
            if (preg_match('/^IDX_[0-9A-F]+$/', $name) === 0) {
                yield $name => $index;
            }
        }
    }

    public function iterateRelations(array $overridingRelationMap): \Generator
    {
        /** @var Table $table */
        foreach ($overridingRelationMap + $this->foreignKeyMap as $name => $value) {
            if ($value instanceof Relation) {
                yield $value;
            } elseif ($value instanceof ForeignKey) {
                yield Relation::fromForeignKey($value);
            } elseif (array_key_exists($name, $this->foreignKeyMap)) {
                yield Relation::fromForeignKey(
                    $this->foreignKeyMap[$name],
                    is_string($value) ? ['line' => $value] : $value,
                );
            }
        }
    }

    public function query(): Builder
    {
        return $this->connection->query()->from($this->name);
    }

    protected function explodeComment(int $index): string
    {
        return explode("\n", $this->comment, 2)[$index] ?? '';
    }

    protected function createIndexMap(SchemaBuilder $builder): array
    {
        $map = [];

        foreach ($builder->getIndexes($this->name) as $raw) {
            $map[$raw['name']] = new Index($raw);
        }

        return $map;
    }

    protected function createForeignKeyMap(SchemaBuilder $builder): array
    {
        $map = [];

        foreach ($builder->getForeignKeys($this->name) as $raw) {
            $map[$raw['name']] = new ForeignKey($raw);
        }

        return $map;
    }

    protected function createColumnMap(SchemaBuilder $builder): array
    {
        $map = [];

        foreach ($builder->getColumns($this->name) as $raw) {
            $map[$raw['name']] = new Column($builder, $this->name, $raw, $this->indexMap, $this->foreignKeyMap);
        }

        return $map;
    }
}
