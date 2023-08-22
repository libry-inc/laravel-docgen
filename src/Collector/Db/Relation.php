<?php

namespace Libry\LaravelDocgen\Collector\Db;

use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Illuminate\Support\Str;

class Relation
{
    public function __construct(
        public readonly string $foreignKeyName,
        public readonly array $localColumnNames = ['id'],
        public readonly ?string $foreignTableName = null,
        public readonly array $foreignColumnNames = ['id'],
        public readonly string $line = '--',
        public readonly ?string $comment = null,
    ) {
        if (is_null($foreignTableName)) {
            if (count($localColumnNames) === 1 && str_ends_with($localColumnNames[0], '_id')) {
                $column = $localColumnNames[0];
            } else {
                throw new \LogicException('"foreignTableName" can be omitted only in case of BelongsTo.');
            }

            $words = preg_split('/(_)/u', substr($column, 0, -3), -1, PREG_SPLIT_DELIM_CAPTURE);
            $lastWord = array_pop($words);
            $this->foreignTableName = implode('', $words).Str::plural($lastWord);
        }
    }

    public static function fromForeignKey(ForeignKeyConstraint $foreignKey): self
    {
        return new self(
            $foreignKey->getName(),
            $foreignKey->getLocalColumns(),
            $foreignKey->getForeignTableName(),
            $foreignKey->getForeignColumns()
        );
    }
}
