@php
/**
 * @var \Libry\LaravelDocgen\Collector\Db\TableCollection<string,\Libry\LaravelDocgen\Collector\Db\Table> $tableCollection
 * @var bool|null $listsRecords
 * @var string[]|null $tableNoteMap - $tableNoteMap[$tableName]: an additional note for the table
 */
$ignoreColumns = config('docgen.ignore_columns_in_listing_records');
$flippedIgnoreColumns = array_flip($ignoreColumns);
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
{{----}}@if($listsRecords ?? false)
{{----}}{{----}}| {!! implode(' | ', array_keys(array_diff_key($table->columnMap, $flippedIgnoreColumns))) !!} |
{{----}}{{----}}| {!! implode(' | ', array_fill(0, count(array_diff_key($table->columnMap, $flippedIgnoreColumns)), '--')) !!} |
{{----}}{{----}}@foreach($table->query()->get() as $record)
{{----}}{{----}}{{----}}| {!! implode(' | ', array_values(array_diff_key((array) $record, $flippedIgnoreColumns))) !!} |
{{----}}{{----}}@endforeach
{{----}}{{----}}
{{----}}@endif
{{----}}@isset($tableNoteMap[$tableName])
{{----}}{{----}}{!! $tableNoteMap[$tableName] !!}
{{----}}{{----}}
{{----}}@endisset
@endforeach
