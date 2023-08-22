<?php

namespace Libry\LaravelDocgen\Collector\Db;

use Doctrine\DBAL\Schema\Column as DbalColumn;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Table as Dbal;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Connection;

class Table
{
    /** @var Column[] key: a column name */
    public readonly array $columnMap;

    /** @var ForeignKeyConstraint[] key: a foreign key name */
    public readonly array $foreignKeyMap;

    /** @var Index[] key: an index name */
    public readonly array $indexMap;

    /** @var Relation[] key: a foreign key name */
    public readonly array $relationMap;

    public function __construct(protected readonly Connection $connection, public readonly Dbal $dbal)
    {
        $this->foreignKeyMap = $dbal->getForeignKeys();
        $this->indexMap = $dbal->getIndexes();
        $this->relationMap = array_map([$this, 'createRelation'], $this->foreignKeyMap);
        $columnMap = [];

        foreach ($dbal->getColumns() as $column) {
            $columnMap[$column->getName()] = $this->createColumn($column);
        }

        $this->columnMap = $columnMap;
    }

    /**
     * Get the logical name of this table
     * - treat the 1st row of this table comment as a logical name.
     */
    public function getLogicalName(): string
    {
        return $this->explodeComment(0);
    }

    /**
     * Get the description of this table
     * - treat the 2nd and after rows of this table comment as a description.
     */
    public function getDescription(): string
    {
        return $this->explodeComment(1);
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
        $nullArguments = [
            'localColumnNames' => null,
            'foreignTableName' => null,
            'foreignColumnNames' => null,
            'line' => null,
            'comment' => null,
        ];

        /** @var Table $table */
        foreach ($overridingRelationMap + $this->relationMap as $foreignKeyName => $overridingRelation) {
            if ($overridingRelation instanceof Relation) {
                yield $overridingRelation;
            } elseif (array_key_exists($foreignKeyName, $this->relationMap)) {
                $original = $this->relationMap[$foreignKeyName];
                $localColumnNames = $original->localColumnNames;
                $foreignTableName = $original->foreignTableName;
                $foreignColumnNames = $original->foreignColumnNames;
                $line = $original->line;
                $comment = $original->comment;

                if (is_string($overridingRelation)) {
                    $line = $overridingRelation;
                } elseif (is_array($overridingRelation)) {
                    extract(array_intersect_key($overridingRelation, $nullArguments));
                } else {
                    throw new \RuntimeException(__METHOD__.': $overridingRelationMap must be an array of string[]|string|Relation.');
                }

                yield new Relation(
                    $foreignKeyName,
                    $localColumnNames,
                    $foreignTableName,
                    $foreignColumnNames,
                    $line,
                    $comment,
                );
            }
        }
    }

    public function query(): Builder
    {
        return $this->connection->query()->from($this->dbal->getName());
    }

    protected function createColumn(DbalColumn $dbalColumn): Column
    {
        return new Column($dbalColumn, $this->foreignKeyMap, $this->indexMap);
    }

    protected function createRelation(ForeignKeyConstraint $foreignKey): Relation
    {
        return Relation::fromForeignKey($foreignKey);
    }

    protected function explodeComment(int $index): string
    {
        return explode("\n", $this->dbal->getComment() ?? '', 2)[$index] ?? '';
    }
}
