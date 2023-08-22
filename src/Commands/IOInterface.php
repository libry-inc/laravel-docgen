<?php

namespace Libry\LaravelDocgen\Commands;

use Illuminate\Console\OutputStyle;

interface IOInterface
{
    public function error($string, $verbosity = null);

    public function info($string, $verbosity = null);

    public function isRunning(): bool;

    /**
     * @return OutputStyle
     */
    public function getOutput();
}
