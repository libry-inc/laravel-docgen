@php
/**
 * @var \Libry\LaravelDocgen\Collector\Db\TableCollection<string,\Libry\LaravelDocgen\Collector\Db\Table> $tableCollection
 * @var ?string $config
 * @var string[]|null $tableNoteMap - $tableNoteMap[$tableName]: an additional note for the table
 * @var string[]|null $relationMap - $relationMap[$tableName][$foreignKeyName]: an additional or overriding relation
 * @var ?string $footer
 */
@endphp
@startuml
@isset($config)
{{----}}{!! $config !!}
@else
{{----}}hide circle
{{----}}skinparam linetype ortho
@endisset

@foreach($tableCollection as $tableName => $table)
{{----}}entity "{!! (($name = $table->getLogicalName()) ? "$name\\n" : '').$tableName !!}" as _{!! $tableName !!} {
{{----}}@if(!is_null($key = $table->dbal->getPrimaryKey()) && (count($columns = $key->getColumns()) !== 1 || $columns[0] !== 'id'))
{{----}}{{----}}@foreach($columns as $column)
{{----}}{{----}}{{----}}    * {!! $column !!}
{{----}}{{----}}@endforeach
{{----}}@endif
{{----}}    --
{{----}}@isset($tableNoteMap[$tableName])
{{----}}{{----}}    {!! implode("\n    ", explode("\n", $tableNoteMap[$tableName])) !!}
{{----}}@endisset
{{----}}}
@endforeach

@foreach($tableCollection as $tableName => $table)
{{----}}@foreach($table->iterateRelations($relationMap[$tableName] ?? []) as $relation)
{{----}}{{----}}@if(isset($relation->line) && isset($tableCollection[$relation->foreignTableName]))
{{----}}{{----}}{{----}}{!!
/*--------------------*/"_$tableName {$relation->line} _{$relation->foreignTableName}"
/*--------------------*/!!}@if($relation->comment !== '') : {!! $relation->comment ?? $relation->foreignKeyName !!}@endif
{{----}}{{----}}{{----}}
{{----}}{{----}}@endif
{{----}}@endforeach
@endforeach
@isset($footer)
{{----}}
{{----}}{!! $footer !!}
@endisset
@enduml
