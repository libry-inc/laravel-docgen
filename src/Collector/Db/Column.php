<?php

namespace Libry\LaravelDocgen\Collector\Db;

use Illuminate\Database\Schema\Builder;
use Illuminate\Database\Schema\Grammars\MySqlGrammar;

/**
 * @property string $logicalName
 * @property string $note
 * @property string blueprintType
 * @property array blueprintOptions
 */
class Column
{
    use Gets;

    public readonly string $name;

    public readonly string $typeName;

    public readonly string $type;

    public readonly ?string $collation;

    public readonly bool $nullable;

    public readonly ?string $default;

    public readonly bool $autoIncrement;

    public readonly string $comment;

    public readonly bool $useCurrentOnUpdate;

    /** @var ForeignKey[] key: a foreign key name */
    public readonly array $foreignKeyMap;

    /** @var Index[] key: an index name */
    public readonly array $indexMap;

    /**
     * @param mixed[] $raw
     * @param Index[] $allIndexMap
     * @param ForeignKey[] $allForeignKeyMap
     */
    public function __construct(Builder $builder, string $table, array $raw, array $allIndexMap, array $allForeignKeyMap)
    {
        $indexMap = [];
        $foreignKeyMap = [];
        $this->name = $selfName = $raw['name'];
        $this->typeName = $raw['type_name'];
        $this->type = $raw['type'];
        $this->collation = $raw['collation'];
        $this->nullable = $raw['nullable'];
        $this->default = $raw['default'];
        $this->autoIncrement = $raw['auto_increment'];
        $this->comment = $raw['comment'] ?? '';
        $this->useCurrentOnUpdate = $this->typeName === 'datetime' && $this->usesCurrentOnUpdate($builder, $table);

        foreach ($allIndexMap as $index) {
            foreach ($index->columns as $name) {
                if ($name === $selfName) {
                    $indexMap[$index->name] = $index;

                    break;
                }
            }
        }

        foreach ($allForeignKeyMap as $foreignKey) {
            foreach ($foreignKey->localColumns as $name) {
                if ($name === $selfName) {
                    $foreignKeyMap[$foreignKey->name] = $foreignKey;

                    break;
                }
            }
        }

        $this->indexMap = $indexMap;
        $this->foreignKeyMap = $foreignKeyMap;
    }

    public function iterateExplicitIndexes(): \Generator
    {
        foreach ($this->indexMap as $name => $index) {
            if (preg_match('/^IDX_[0-9A-F]+$/', $name) === 0) {
                yield $name => $index;
            }
        }
    }

    /**
     * Get a Blueprint method with arguments.
     */
    protected function getBlueprintType(): string
    {
        return match ($this->typeName) {
            'bigint' => $this->getBlueprintIntegerType('big'),
            'int' => $this->getBlueprintIntegerType(''),
            'smallint' => $this->getBlueprintIntegerType('small'),
            'tinyint' => $this->type === 'tinyint(1)' ? 'boolean' : $this->getBlueprintIntegerType('tiny'),
            'decimal' => $this->type === 'decimal(8,2)' ? 'decimal' : $this->type,
            'blob' => 'binary',
            'varbinary' => preg_replace('/^var/', '', $this->type),
            'binary' => preg_replace('/\)$/', ',true)', $this->type),
            'datetime' => str_replace('datetime', 'dateTime', $this->type),
            'varchar' => $this->type === 'varchar('.Builder::$defaultStringLength.')' ? 'string' : str_replace('varchar', 'string', $this->type),
            'char' => $this->type === 'char('.Builder::$defaultStringLength.')' ? 'char' : $this->type,
            'longtext' => 'longText',
            'mediumtext' => 'mediumText',
            'tinytext' => 'tinyText',
            // double, date, time, text, enum
            default => $this->type,
        };
    }

    protected function getBlueprintIntegerType(string $size): string
    {
        $suffix = 'Integer';

        if (str_ends_with($this->type, ' unsigned')) {
            if ($this->autoIncrement) {
                $suffix = 'Increments';
            } else {
                $size = 'unsigned'.ucfirst($size);
            }
        }

        return lcfirst($size.$suffix);
    }

    /**
     * Get a Blueprint options.
     */
    protected function getBlueprintOptions(): array
    {
        $options = [];

        if ($this->autoIncrement) {
            $options[] = 'autoIncrement';
        }

        if ($this->nullable) {
            $options[] = 'nullable';
        }

        if (!is_null($default = $this->default)) {
            if ($this->typeName === 'datetime' && str_starts_with($default, 'CURRENT_TIMESTAMP')) {
                $options[] = 'useCurrent';
            } elseif (is_numeric($default)) {
                $options[] = 'default('.$default.')';
            } else {
                $options[] = 'default('.json_encode($default, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).')';
            }
        }

        if ($this->useCurrentOnUpdate) {
            $options[] = 'useCurrentOnUpdate';
        }

        return $options;
    }

    /**
     * Get the logical name of this column
     * - treat the 1st row of this column comment as a logical name.
     */
    protected function getLogicalName(): string
    {
        return $this->explodeComment(0);
    }

    /**
     * Get the note of this column
     * - treat the 2nd and after rows of this column comment as a note.
     */
    protected function getNote(): string
    {
        return $this->explodeComment(1);
    }

    protected function explodeComment(int $index): string
    {
        return explode("\n", $this->comment, 2)[$index] ?? '';
    }

    protected function usesCurrentOnUpdate(Builder $builder, string $table): bool
    {
        $connection = $builder->getConnection();

        if (!$connection->getSchemaGrammar() instanceof MySqlGrammar) {
            return false;
        }

        $extra = $connection->scalar(
            'select `extra` from information_schema.columns where table_schema = ? and table_name = ? and column_name = ?',
            [$connection->getDatabaseName(), $connection->getTablePrefix().$table, $this->name],
            false
        );

        return strpos($extra, 'on update CURRENT_TIMESTAMP') !== false;
    }
}
