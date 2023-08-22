<?php

namespace Libry\LaravelDocgen\Deployer;

class StandardOutputDeployer implements DeployerInterface
{
    public function setFilename(string $filename): void
    {
    }

    public function deploy(string $document): void
    {
        echo $document;
    }
}
