<?php

namespace Libry\LaravelDocgen\Collector;

use Illuminate\Console\OutputStyle;

interface CollectorInterface
{
    public function getPathsToWatch(): array;

    public function refresh(OutputStyle $output): void;
}
