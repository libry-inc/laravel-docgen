@php
/**
 * @var \Libry\LaravelDocgen\Collector\Db\DbCollector $collector
 * @var \Libry\LaravelDocgen\Deployer\OutputInterface $output
 */
$output->setFilename('sample.md');
@endphp

# ERD
@include('db.shared.erd_in_md', [
    'tableCollection' => $collector->getTableCollection(),
])

## Customized
@include('db.shared.erd_in_md', [
    'tableCollection' => $collector->getTableCollection(['groups', 'members', 'tests']),
    'config' => 'hide circle',
    'tableNoteMap' => ['groups' => 'groups can have up to 10 members.'],
    'relationMap' => [
        'tests' => [
            'custom_fk1' => new \Libry\LaravelDocgen\Collector\Db\Relation('custom_fk1', foreignTableName: 'members'),
        ],
        'members' => [
            'members_group_id_foreign' => '}o-||',
        ],
    ],
    'footer' => <<<'EOS'
entity custom_tables {}
EOS,
])

# Tables
@include('db.shared.tables', [
    'tableCollection' => $collector->getTableCollection(),
])
