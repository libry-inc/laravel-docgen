<?php

namespace Libry\LaravelDocgen\Collector;

use Libry\LaravelDocgen\Factory;

class CollectorFactory extends Factory
{
    public function create(?string $driver): CollectorInterface
    {
        return $this->instantiate('collectors', $driver ?: config('docgen.default_collector'));
    }
}
