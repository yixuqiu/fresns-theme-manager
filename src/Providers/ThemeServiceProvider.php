<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace Fresns\ThemeManager\Providers;

use Illuminate\Support\ServiceProvider;

class ThemeServiceProvider extends ServiceProvider
{
    public function boot()
    {
        //
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/themes.php', 'themes');
        $this->publishes([
            __DIR__.'/../../config/themes.php' => config_path('themes.php'),
        ], 'laravel-theme-config');

        $this->registerCommands([
            __DIR__.'/../Commands/*',
        ]);
    }

    public function registerCommands($paths)
    {
        $allCommand = [];

        foreach ($paths as $path) {
            $commandPaths = glob($path);

            foreach ($commandPaths as $command) {
                $commandPath = realpath($command);
                if (! is_file($commandPath)) {
                    continue;
                }

                $commandClass = 'Fresns\\ThemeManager\\Commands\\'.pathinfo($commandPath, PATHINFO_FILENAME);

                if (class_exists($commandClass)) {
                    $allCommand[] = $commandClass;
                }
            }
        }

        $this->commands($allCommand);
    }
}
