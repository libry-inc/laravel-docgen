<?php

namespace Libry\LaravelDocgen\Deployer;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemManager;

class FilesystemDeployer implements DeployerInterface
{
    private string $filename = 'default';

    private Filesystem $filesystem;

    public function __construct(FilesystemManager $filesystemManager, array $config)
    {
        $this->filesystem = $filesystemManager->disk($config['disk']);
    }

    public function setFilename(string $filename): void
    {
        $this->filename = $filename;
    }

    public function deploy(string $document): void
    {
        $this->filesystem->put($this->filename, $document);
    }
}
