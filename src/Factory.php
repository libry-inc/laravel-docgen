<?php

namespace Libry\LaravelDocgen;

use Illuminate\Contracts\Container\Container;

abstract class Factory
{
    protected Container $app;

    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    protected function instantiate(string $context, string $driver)
    {
        $config = $this->getConfig($context, $driver);
        $class = $this->getClass($context, $driver);

        return $this->app->make($class, ['config' => $config]);
    }

    private function getConfig(string $context, string $driver): array
    {
        $config = config("docgen.{$context}.{$driver}");

        if (!is_array($config)) {
            throw new \RuntimeException("{$driver} is invalid or not found in config('docgen.{$context}')");
        }

        return $config;
    }

    private function getClass(string $context, string $driver): string
    {
        $class = config("docgen.{$context}.{$driver}.class");

        if (!is_string($class) || !class_exists($class) && !$this->app->has($class)) {
            throw new \RuntimeException("config('docgen.{$context}.{$driver}.class') is not a class");
        }

        return $class;
    }
}
