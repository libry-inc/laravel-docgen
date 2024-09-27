@php
/**
 * @var \Libry\LaravelDocgen\Collector\Db\TableCollection<string,\Libry\LaravelDocgen\Collector\Db\Table> $tableCollection
 * @var ?string $config
 * @var string[]|null $tableNoteMap - $tableNoteMap[$tableName]: an additional note for the table
 * @var (\Libry\LaravelDocgen\Collector\Db\Relation|array|string)[]|null $relationMap - $relationMap[$tableName][$foreignKeyName]: an additional or overriding relation
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
{{----}}entity "{!! (($name = $table->logicalName) ? "$name\\n" : '').$tableName !!}" as _{!! $tableName !!} {
{{----}}@if(!is_null($primary = $table->primary) && (count($keys = $primary->columns) !== 1 || $keys[0] !== 'id'))
{{----}}{{----}}@foreach($keys as $key)
{{----}}{{----}}{{----}}    * {!! $key !!}
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
{{----}}{{----}}@if($relation->line !== '' && isset($tableCollection[$relation->foreignTable]))
{{----}}{{----}}{{----}}{!!
/*--------------------*/"_$tableName {$relation->line} _{$relation->foreignTable}"
/*--------------------*/!!}@if($relation->comment !== '') : {!! $relation->comment ?? $relation->name !!}@endif
{{----}}{{----}}{{----}}
{{----}}{{----}}@endif
{{----}}@endforeach
@endforeach
@isset($footer)
{{----}}
{{----}}{!! $footer !!}
@endisset
@enduml
