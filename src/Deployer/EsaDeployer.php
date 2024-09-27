<?php

namespace Libry\LaravelDocgen\Deployer;

class EsaDeployer implements DeployerInterface
{
    private string $filename = 'default';

    public function setFilename(string $filename): void
    {
        dd(debug_backtrace());
        $this->filename = $filename;
    }

    public function deploy(string $document): void
    {
        // $this->filesystem->put($this->filename, $document);
    }
}
