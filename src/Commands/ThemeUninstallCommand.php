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
use Fresns\ThemeManager\Support\Process;

class ThemeUninstallCommand extends Command
{
    protected $signature = 'theme:uninstall {name}
        {--cleardata : Trigger clear theme data}';

    protected $description = 'Install the theme from the specified path';

    public function handle()
    {
        try {
            $unikey = $this->argument('name');

            event('theme:uninstalling', [[
                'unikey' => $unikey,
            ]]);

            if ($this->option('cleardata')) {
                event('themes.cleandata', [[
                    'unikey' => $unikey,
                ]]);
            }

            $this->call('theme:unpublish', [
                'name' => $unikey,
            ]);

            $theme = new Theme($unikey);
            File::deleteDirectory($theme->getThemePath());

            // Triggers top-level computation of composer.json hash values and installation of extension themes
            Process::run('composer update', $this->output);

            event('theme:uninstalled', [[
                'unikey' => $unikey,
            ]]);

            $this->info("Uninstalled: {$unikey}");
        } catch (\Throwable $e) {
            $this->error("Uninstall fail: {$e->getMessage()}");
        }

        return 0;
    }
}
