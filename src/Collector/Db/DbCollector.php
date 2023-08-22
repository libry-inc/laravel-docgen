<?php

namespace Libry\LaravelDocgen\Collector\Db;

use Illuminate\Console\OutputStyle;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Libry\LaravelDocgen\Collector\CollectorInterface;

class DbCollector implements CollectorInterface
{
    protected string $connection;

    protected array $migrationPaths;

    public function __construct(array $config)
    {
        $this->connection = $config['connection'] ?? $this->getDefaultConnectionName();
        $this->migrationPaths = $config['migration_paths'] ?? $this->getDefaultMigrationPaths();
    }

    public function getPathsToWatch(): array
    {
        return $this->migrationPaths;
    }

    public function refresh(OutputStyle $output): void
    {
        // Execute by a new process not to read old php file loaded previously
        $commands = Arr::flatten(array_filter([
            'php',
            escapeshellcmd(base_path('artisan')),
            'migrate:refresh',
            '--database',
            escapeshellarg($this->connection),
            array_map(
                static fn (string $path) => ['--path', escapeshellarg($path)],
                $this->migrationPaths
            ),
            $this->isMigrationPathsAbsolute() ? '--realpath' : null,
            '--ansi',
            '--no-interaction',
            '2>&1',
        ]));
        exec(implode(' ', $commands), $results, $code);
        $output->writeln($results);

        if ($code !== 0) {
            $output->error('Exited with '.$code);
        }
    }

    public function getTableCollection(array $tableNames = ['*']): TableCollection
    {
        $patterns = array_values($this->createTableNamePatterns($tableNames));
        $ignorePatterns = array_values($this->createTableNameIgnorePatterns($tableNames));
        $builder = $this->getSchemaBuilder();
        $connection = $builder->getConnection();
        $this->initializeConnection($connection);
        $tableMap = [];

        foreach ($builder->getAllTables() as $row) {
            $tableName = head((array) $row);

            foreach ($ignorePatterns as $ignorePattern) {
                if (preg_match($ignorePattern, $tableName) === 1) {
                    continue 2;
                }
            }

            foreach ($patterns as $patternIndex => $pattern) {
                if (preg_match($pattern, $tableName) !== 1) {
                    continue;
                }

                $tableMap[$patternIndex][$tableName] = $this->createTable($connection, $tableName);

                break;
            }
        }

        ksort($tableMap, SORT_NUMERIC);

        return $this->createTableCollection(call_user_func_array('array_merge', $tableMap));
    }

    protected function getDefaultConnectionName(): string
    {
        return config('database.default');
    }

    protected function getDefaultMigrationPaths(): array
    {
        return [];
    }

    protected function getDefaultIgnoreTableNames(): array
    {
        return [config('database.migrations')];
    }

    protected function getSchemaBuilder(): Builder
    {
        return Schema::connection($this->connection);
    }

    protected function initializeConnection(Connection $connection): void
    {
        $schemaManager = $connection->getDoctrineSchemaManager();
        // treat enums as a string
        $schemaManager->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
    }

    protected function createTableCollection(Arrayable|iterable $tables): TableCollection
    {
        return new TableCollection($this->connection, $tables);
    }

    protected function createTable(Connection $connection, string $tableName): Table
    {
        return new Table($connection, $connection->getDoctrineSchemaManager()->introspectTable($tableName));
    }

    protected function createTableNamePatterns(array $tableNames): array
    {
        $patterns = [];

        foreach ($tableNames as $tableName) {
            if (!str_starts_with($tableName, '!')) {
                $pattern = $this->convertToRegExp($tableName);
                $patterns[$pattern] = $pattern;
            }
        }

        return $patterns;
    }

    protected function createTableNameIgnorePatterns(array $tableNames): array
    {
        $ignorePatterns = [];

        foreach ($tableNames as $tableName) {
            if (str_starts_with($tableName, '!')) {
                $pattern = $this->convertToRegExp(substr($tableName, 1));
                $ignorePatterns[$pattern] = $pattern;
            }
        }

        return array_merge($ignorePatterns, $this->createTableNamePatterns($this->getDefaultIgnoreTableNames()));
    }

    protected function convertToRegExp(string $value): string
    {
        return str_starts_with($value, '/') ? $value : '/^'.str_replace('*', '.*', $value).'$/';
    }

    protected function isMigrationPathsAbsolute(): bool
    {
        foreach ($this->migrationPaths as $path) {
            if (str_starts_with($path, '/')) {
                return true;
            }
        }

        return false;
    }
}
