<?php

namespace Libry\LaravelDocgen;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Libry\LaravelDocgen\Collector\CollectorFactory;
use Libry\LaravelDocgen\Collector\CollectorInterface;
use Libry\LaravelDocgen\Commands\IOInterface;
use Libry\LaravelDocgen\Deployer\DeployerFactory;
use Libry\LaravelDocgen\Deployer\DeployerInterface;

class Documenter
{
    public function __construct(
        protected CollectorFactory $collectorFactory,
        protected DeployerFactory $deployerFactory,
        protected ViewFactory $viewFactory,
    ) {}

    public function watch(string $path, ?string $collectorDriver, ?string $deployerDriver, bool $refreshes, IOInterface $io): void
    {
        $collector = $this->collectorFactory->create($collectorDriver);
        $viewFileWatcher = new FileWatcher($this->getPathsToWatch());
        $collectionFileWatcher = $refreshes ? new FileWatcher($collector->getPathsToWatch()) : null;

        while ($io->isRunning()) {
            sleep(1);

            $viewFileWatcher->refresh();
            $collectionFileWatcher?->refresh();

            if (!$viewFileWatcher->hasChanged() && !$collectionFileWatcher?->hasChanged()) {
                continue;
            }

            $errors = array_merge($viewFileWatcher->getSyntaxErrors(), $collectionFileWatcher?->getSyntaxErrors() ?? []);

            if (count($errors) > 0) {
                $io->error(implode("\n\n", $errors));

                continue;
            }

            try {
                if ($collectionFileWatcher?->hasChanged()) {
                    $io->info('['.now().'] refreshing...');
                    $collector->refresh($io->getOutput());
                    $io->info('['.now().'] refreshed.');
                }

                // Execute by a new process not to read old php file loaded previously
                $io->info('['.now().'] rendering...');
                $commands = [
                    'php',
                    escapeshellcmd(base_path('artisan')),
                    'laravel-docgen',
                    escapeshellarg($path),
                    '--ansi',
                ];

                if (!is_null($collectorDriver)) {
                    array_push($commands, '--collector', escapeshellarg($collectorDriver));
                }

                if (!is_null($deployerDriver)) {
                    array_push($commands, '--deployer', escapeshellarg($deployerDriver));
                }

                $results = [];
                exec(implode(' ', $commands).' 2>&1', $results, $code);
                $io->getOutput()->writeln($results);

                if ($code !== 0) {
                    $io->getOutput()->error('Exited with '.$code);
                }

                $io->info('['.now().'] rendered.');
            } catch (\Throwable $e) {
                $io->error("{$e->getCode()}: {$e->getMessage()}\n    at {$e->getFile()}:{$e->getLine()}\n{$e->getTraceAsString()}");
            }
        }
    }

    public function execute(string $path, ?string $collectorDriver, ?string $deployerDriver): void
    {
        $this->document($path, $this->collectorFactory->create($collectorDriver), $this->deployerFactory->create($deployerDriver));
    }

    protected function document(string $path, CollectorInterface $collector, DeployerInterface $deployer): void
    {
        $output = $deployer;
        $view = $this->viewFactory->make($path, compact('collector', 'output'));
        $document = $view->render();
        $deployer->deploy($document);
    }

    protected function getPathsToWatch(): array
    {
        return config('laravel-docgen.template_paths');
    }
}
