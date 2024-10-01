<?php

use Illuminate\Foundation\Application;
use Libry\LaravelDocgen\Commands\Run;

return Application::configure(dirname(__DIR__))
    ->withCommands([Run::class])
    ->withExceptions()
    ->create()
;
