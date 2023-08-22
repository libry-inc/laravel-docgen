<?php

namespace Libry\LaravelDocgen\Deployer;

interface DeployerInterface extends OutputInterface
{
    public function deploy(string $document): void;
}
