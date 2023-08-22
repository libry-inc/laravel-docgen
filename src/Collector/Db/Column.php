<?php

namespace Libry\LaravelDocgen\Collector\Db;

use Doctrine\DBAL\Schema\Column as Dbal;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Types\BigIntType;
use Doctrine\DBAL\Types\BinaryType;
use Doctrine\DBAL\Types\BlobType;
use Doctrine\DBAL\Types\BooleanType;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Types\DateType;
use Doctrine\DBAL\Types\DecimalType;
use Doctrine\DBAL\Types\FloatType;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\SimpleArrayType;
use Doctrine\DBAL\Types\SmallIntType;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\TextType;
use Doctrine\DBAL\Types\TimeType;

class Column
{
    /** @var ForeignKeyConstraint[] key: a foreign key name */
    public readonly array $foreignKeyMap;

    /** @var Index[] key: an index name */
    public readonly array $indexMap;

    public function __construct(public readonly Dbal $dbal, array $foreignKeys, array $indexes)
    {
        $foreignKeyMap = [];
        $indexMap = [];

        $selfName = $dbal->getName();

        /** @var ForeignKeyConstraint $foreignKey */
        foreach ($foreignKeys as $foreignKey) {
            foreach ($foreignKey->getLocalColumns() as $name) {
                if ($name === $selfName) {
                    $foreignKeyMap[$foreignKey->getName()] = $foreignKey;

                    break;
                }
            }
        }

        /** @var Index $index */
        foreach ($indexes as $index) {
            foreach ($index->getColumns() as $name) {
                if ($name === $selfName) {
                    $indexMap[$index->getName()] = $index;

                    break;
                }
            }
        }

        $this->foreignKeyMap = $foreignKeyMap;
        $this->indexMap = $indexMap;
    }

    /**
     * Get a Blueprint method with arguments.
     */
    public function getBlueprintType(): string
    {
        $dbal = $this->dbal;
        $type = $dbal->getType();
        $class = get_class($type);

        switch ($class) {
            case BigIntType::class:
                return $dbal->getUnsigned()
                    ? ($dbal->getAutoincrement() ? 'bigIncrements' : 'unsignedBigInteger')
                    : 'bigInteger';

            case BinaryType::class:
                // - MySQL treat a binary in the context of Blueprint as a `BLOB` (not `BINARY` or `VARBINARY`).
                // - Blueprint does not support varbinary, smallBlob, blob, etc.
                return ($dbal->getFixed() ? 'binary' : 'varbinary')
                    .($dbal->getLength() !== 255 ? "({$dbal->getLength()})" : '');

            case BlobType::class:
                switch ($dbal->getLength()) {
                    case 255:
                        return 'tinyBlob';

                    case 65535:
                        // Blueprint does not support a like `blob`.
                        return 'binary';

                    case 16777215:
                        return 'mediumBlob';

                    case 4294967295:
                        return 'longBlob';
                }

                throw new \RuntimeException("invalid blob length - {$dbal->getName()} TEXT({$dbal->getLength()})");

            case BooleanType::class:
                return $dbal->getUnsigned() ? 'unsignedTinyInteger' : 'boolean';

            case DateTimeType::class:
                return 'dateTime'.($dbal->getLength() !== 0 ? "({$dbal->getLength()})" : '');

            case DateType::class:
                return 'date';

            case DecimalType::class:
                return ($dbal->getUnsigned() ? 'unsignedDecimal' : 'decimal')
                    .($dbal->getPrecision() !== 8 && $dbal->getScale() !== 2 ? "({$dbal->getPrecision()}, {$dbal->getScale()})" : '');

            case FloatType::class:
                // - DBAL does not have a like `DoubleType`.
                // - In MySQL 8.0.17, `FLOAT(M, D)`, `DOUBLE(M, D)` is deprecated.
                // - MySQL treat a float in the context of Blueprint as a double.
                // -> Omit M and D for simplicity.
                return $dbal->getUnsigned() ? 'unsignedDouble' : 'double';

            case IntegerType::class:
                return $dbal->getUnsigned()
                    ? ($dbal->getAutoincrement() ? 'increments' : 'unsignedInteger')
                    : 'integer';

            case SimpleArrayType::class:
                // not support `SET`, convert to a string
                return 'string';

            case SmallIntType::class:
                return $dbal->getUnsigned()
                    ? ($dbal->getAutoincrement() ? 'smallIncrements' : 'unsignedSmallInteger')
                    : 'smallInteger';

            case StringType::class:
                // treat an enum as `StringType (Length: 0)`
                $length = $dbal->getLength() ?: 255;

                return $dbal->getFixed()
                    ? "char({$length})"
                    : ($length !== 255 ? "string({$length})" : 'string');

            case TextType::class:
                switch ($dbal->getLength()) {
                    case 255:
                        return 'tinyText';

                    case 65535:
                        return 'text';

                    case 16777215:
                        return 'mediumText';

                    case 0:
                    case 4294967295:
                        return 'longText';
                }

                throw new \RuntimeException("invalid text length - {$dbal->getName()} TEXT({$dbal->getLength()})");

            case TimeType::class:
                return $dbal->getLength() !== 0 ? "time({$dbal->getLength()})" : 'time';

            default:
                throw new \RuntimeException("unsupported type {$class} - {$dbal->getName()}");
        }
    }

    /**
     * Get a Blueprint options.
     */
    public function getBlueprintOptions(): array
    {
        $options = [];

        if ($this->dbal->getAutoincrement() && (!$this->dbal->getUnsigned() || $this->dbal->getType() instanceof DecimalType)) {
            $options[] = 'autoIncrement';
        }

        if (!$this->dbal->getNotnull()) {
            $options[] = 'nullable';
        }

        if (null !== ($default = $this->dbal->getDefault())) {
            if (is_numeric($default)) {
                $options[] = "default({$default})";
            } elseif (preg_match('/^CURRENT_TIMESTAMP(?:\(\d\))?$/', $default) === 1) {
                $options[] = 'useCurrent';
            } else {
                $options[] = 'default('.json_encode($default, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).')';
            }
        }

        return $options;
    }

    /**
     * Get the logical name of this column
     * - treat the 1st row of this column comment as a logical name.
     */
    public function getLogicalName(): string
    {
        return $this->explodeComment(0);
    }

    /**
     * Get the note of this column
     * - treat the 2nd and after rows of this column comment as a note.
     */
    public function getNote(): string
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

    protected function explodeComment(int $index): string
    {
        return explode("\n", $this->dbal->getComment() ?? '', 2)[$index] ?? '';
    }
}
