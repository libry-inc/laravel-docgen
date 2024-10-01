<?php

namespace Libry\LaravelDocgen;

class FileWatcher
{
    protected array $existingTimestampMap = [];

    protected array $deletedTimestampMap = [];

    protected array $errorTimestampMap = [];

    protected array $changedTimestampMap;

    public function __construct(protected array $paths) {}

    public function refresh(): void
    {
        $this->changedTimestampMap = [];
        $this->deletedTimestampMap = $this->existingTimestampMap;

        foreach ($this->scanPhpFiles() as $path) {
            $timestamp = filemtime($path);
            $existsOld = key_exists($path, $this->existingTimestampMap);

            if ($existsOld) {
                unset($this->deletedTimestampMap[$path]);
                clearstatcache(false, $path);
            }

            if (!$existsOld || $timestamp !== $this->existingTimestampMap[$path]) {
                $this->changedTimestampMap[$path] = $timestamp;
                $this->existingTimestampMap[$path] = $timestamp;
            }
        }

        foreach (array_keys($this->deletedTimestampMap) as $path) {
            unset($this->existingTimestampMap[$path], $this->errorTimestampMap[$path]);
        }
    }

    public function hasChanged(): bool
    {
        return count($this->changedTimestampMap) > 0 || count($this->deletedTimestampMap) > 0;
    }

    public function getSyntaxErrors(): array
    {
        $errors = [];

        foreach ($this->changedTimestampMap + $this->errorTimestampMap as $path => $timestamp) {
            $error = $this->getSyntaxError($path);

            if (is_null($error)) {
                unset($this->errorTimestampMap[$path]);
            } else {
                $errors[] = $error;
                $this->errorTimestampMap[$path] = $timestamp;
            }
        }

        return $errors;
    }

    /**
     * @return iterable<string,\SplFileInfo>
     */
    protected function scanPhpFiles(): iterable
    {
        foreach ($this->paths as $path) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(
                    $path,
                    \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::CURRENT_AS_PATHNAME
                )
            );

            foreach ($iterator as $filePath) {
                if (str_ends_with($filePath, '.php')) {
                    yield $filePath;
                }
            }
        }
    }

    protected function getSyntaxError(string $path): ?string
    {
        if (array_key_exists($path, $this->deletedTimestampMap)) {
            return null;
        }

        exec('php -l '.escapeshellarg($path).' 2>&1', $outputs, $code);

        if ($code === 0) {
            return null;
        }

        return implode("\n", $outputs);
    }
}
