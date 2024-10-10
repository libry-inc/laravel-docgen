<?php

namespace Libry\LaravelDocgen\Commands;

use Illuminate\Console\Command;
use Libry\LaravelDocgen\Documenter;

class Run extends Command implements IOInterface
{
    protected $signature = <<<'EOS'
        docgen
        {--c|collector= : a key of config('docgen.collectors')}
        {--d|deployer= : a key of config('docgen.deployers')}
        {--w|watch : document each time the file in path changes}
        {--r|refresh-collector : <fg=yellow>[DANGER]</> watch files related to the collector and re-collect, such as refreshing all migrations}
        {path : a view path from config('docgen.collectors.*.template_paths')}
        EOS;

    protected $description = 'Auto-generate a document by collecting data from DB, etc.';

    private $running = true;

    public function handle(Documenter $documenter): int
    {
        $refreshes = $this->option('refresh-collector');
        if ($refreshes && !$this->option('no-interaction')) {
            $this->warn('[DANGER] watch files related to the collector and re-collect, such as refresh all migrations');
            $this->line('( can skip this confirmation with --no-interaction, -n )');

            if ($this->ask('Do you really wish to run this command? [y/N]') !== 'y') {
                $this->line('canceled.');

                return 1;
            }
        }

        if ($refreshes || $this->option('watch')) {
            $this->line('[CTRL \] stop safely.');
            $this->trap(SIGQUIT, fn () => $this->running = false);
            $documenter->watch($this->argument('path'), $this->option('collector'), $this->option('deployer'), $refreshes, $this);
            $this->line('');
        } else {
            $documenter->execute($this->argument('path'), $this->option('collector'), $this->option('deployer'));
        }

        return 0;
    }

    public function isRunning(): bool
    {
        return $this->running;
    }
}
