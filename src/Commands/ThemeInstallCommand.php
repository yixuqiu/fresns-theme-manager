<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace Fresns\ThemeManager\Commands;

use Fresns\ThemeManager\Theme;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ThemeInstallCommand extends Command
{
    protected $signature = 'theme:install {path}
        {--seed}
        {--is_dir}
        ';

    protected $description = 'Install the theme from the specified path';

    public function handle()
    {
        try {
            $path = $this->argument('path');

            if ($this->option('is_dir')) {
                $pluginDirectory = $path;

                if (strpos($pluginDirectory, '/') == false) {
                    $pluginDirectory = "extensions/themes/{$pluginDirectory}";
                }

                if (str_starts_with($pluginDirectory, '/')) {
                    $pluginDirectory = realpath($pluginDirectory);
                } else {
                    $pluginDirectory = realpath(base_path($pluginDirectory));
                }

                $path = $pluginDirectory;
            }

            if (!$path || !file_exists($path)) {
                $this->error('Failed to unzip, couldn\'t find the theme path');

                return Command::FAILURE;
            }

            $extensionPath = str_replace(base_path().'/', '', config('themes.paths.themes'));
            if (! str_contains($path, $extensionPath)) {
                $exitCode = $this->call('theme:unzip', [
                    'path' => $path,
                ]);

                if ($exitCode != 0) {
                    return $exitCode;
                }

                $unikey = Cache::pull('install:theme_unikey');
            } else {
                $unikey = basename($path);
            }

            if (! $unikey) {
                info('Failed to unzip, couldn\'t get the theme unikey');

                return Command::FAILURE;
            }

            $theme = new Theme($unikey);
            if (! $theme->isValidTheme()) {
                $this->error('theme is invalid');

                return Command::FAILURE;
            }

            $theme->manualAddNamespace();

            event('theme:installing', [[
                'unikey' => $unikey,
            ]]);

            $exitCode = $this->call('theme:publish', [
                'name' => $theme->getStudlyName(),
            ]);

            if ($exitCode != 0) {
                return $exitCode;
            }

            event('theme:installed', [[
                'unikey' => $unikey,
            ]]);

            $this->info("Installed: {$theme->getStudlyName()}");
        } catch (\Throwable $e) {
            $this->error("Install fail: {$e->getMessage()}");

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
