<?php

namespace Libry\LaravelDocgen\Tools;

use Illuminate\Foundation\Console\Kernel;

class DevKernel extends Kernel
{
    protected function commands(): void
    {
        $this->load(base_path('src/Commands'));
    }
}
