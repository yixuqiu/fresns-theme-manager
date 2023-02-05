<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace Fresns\ThemeManager\Commands;

use Fresns\ThemeManager\Theme;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ThemeUninstallCommand extends Command
{
    use Traits\WorkThemeNameTrait;

    protected $signature = 'theme:uninstall {name}
        {--cleardata : Trigger clear theme data}';

    protected $description = 'Install the theme from the specified path';

    public function handle()
    {
        try {
            $themeName = $this->getThemeName();
            $theme = new Theme($themeName);

            if ($this->validateThemeRootPath($theme)) {
                $this->error('Failed to operate themes root path');

                return Command::FAILURE;
            }

            event('theme:uninstalling', [[
                'unikey' => $themeName,
            ]]);

            if ($this->option('cleardata')) {
                event('themes.cleandata', [[
                    'unikey' => $themeName,
                ]]);
            }

            $this->call('theme:unpublish', [
                'name' => $themeName,
            ]);

            File::deleteDirectory($theme->getThemePath());

            event('theme:uninstalled', [[
                'unikey' => $themeName,
            ]]);

            $this->info("Uninstalled: {$themeName}");
        } catch (\Throwable $e) {
            $this->error("Uninstall fail: {$e->getMessage()}");

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
