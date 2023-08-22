@php
/**
 * @var \Libry\LaravelDocgen\Collector\Db\TableCollection<string,\Libry\LaravelDocgen\Collector\Db\Table> $tableCollection
 * @var ?string $config
 * @var string[]|null $tableNoteMap - $tableNoteMap[$tableName]: an additional note for the table
 * @var string[]|null $relationMap - $relationMap[$tableName][$foreignKeyName]: an additional or overriding relation
 * @var ?string $footer
 */
@endphp
```puml
@include('db.shared.erd')
```
