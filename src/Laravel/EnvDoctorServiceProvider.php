<?php

namespace Smony\EnvDoctor\Laravel;

use Illuminate\Support\ServiceProvider;
use Smony\EnvDoctor\Console\DoctorCommand;

class EnvDoctorServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                DoctorCommand::class,
            ]);
        }
    }
}