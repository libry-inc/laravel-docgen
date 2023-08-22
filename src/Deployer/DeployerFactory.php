<?php

namespace Libry\LaravelDocgen\Deployer;

use Libry\LaravelDocgen\Factory;

class DeployerFactory extends Factory
{
    public function create(?string $driver): DeployerInterface
    {
        return $this->instantiate('deployers', $driver ?: config('laravel-docgen.default_deployer'));
    }
}
