@php
/**
 * @var \Libry\LaravelDocgen\Collector\Db\TableCollection<string,\Libry\LaravelDocgen\Collector\Db\Table> $tableCollection
 * @var string[]|null $tableNoteMap - $tableNoteMap[$tableName]: an additional note for the table
 */
@endphp
@foreach($tableCollection as $tableName => $table)
{{----}}### {!! $tableName !!}
{{----}}##### {!! $table->logicalName !!}
{{----}}{!! $table->description !!}
{{----}}
{{----}}| Label | Name | Type | Detail | Key | Note |
{{----}}| -- | -- | -- | -- | -- | -- |
{{----}}@foreach($table->columnMap as $columnName => $column)
{{----}}{{----}}| {!!
/*------------*/implode(' | ', [
/*------------*/    $column->logicalName,
/*------------*/    $columnName,
/*------------*/    $column->blueprintType,
/*------------*/    implode('<br>', $column->blueprintOptions),
/*------------*/    implode('<br>', array_keys($column->foreignKeyMap + iterator_to_array($column->iterateExplicitIndexes()))),
/*------------*/    $column->note,
/*------------*/])
/*------------*/!!} |
{{----}}@endforeach
{{----}}
{{----}}{!! $table2note[$tableName] ?? '' !!}
{{----}}
@endforeach
