@php
/**
 * @var \Libry\LaravelDocgen\Collector\Db\TableCollection<string,\Libry\LaravelDocgen\Collector\Db\Table> $tableCollection
 * @var string[]|null $tableNoteMap - $tableNoteMap[$tableName]: an additional note for the table
 */
@endphp
@foreach($tableCollection as $tableName => $table)
{{----}}### {!! $tableName !!}
{{----}}##### {!! $table->getLogicalName() !!}
{{----}}{!! $table->getDescription() !!}
{{----}}
{{----}}| Label | Name | Type | Detail | Key | Note |
{{----}}| -- | -- | -- | -- | -- | -- |
{{----}}@foreach($table->columnMap as $columnName => $column)
{{----}}{{----}}| {!!
/*------------*/implode(' | ', [
/*------------*/    $column->getLogicalName(),
/*------------*/    $columnName,
/*------------*/    $column->getBlueprintType(),
/*------------*/    implode('<br>', $column->getBlueprintOptions()),
/*------------*/    implode('<br>', array_keys($column->foreignKeyMap + $column->indexMap)),
/*------------*/    $column->getNote(),
/*------------*/])
/*------------*/!!} |
{{----}}@endforeach
{{----}}
{{----}}{!! $table2note[$tableName] ?? '' !!}
{{----}}
@endforeach
