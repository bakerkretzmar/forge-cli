<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Forge\Forge;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->loadConfigurationFile();

        $this->app->singleton(Forge::class, fn () => new Forge(config('forge.token')));
    }

    protected function loadConfigurationFile()
    {
        $builtInConfig = config('forge');

        $configFile = implode(DIRECTORY_SEPARATOR, [
            $_SERVER['HOME'] ?? $_SERVER['USERPROFILE'] ?? __DIR__,
            '.forge',
            'config.php',
        ]);

        if (file_exists($configFile)) {
            $globalConfig = require $configFile;
            config()->set('forge', array_merge($builtInConfig, $globalConfig));
        }
    }
}
