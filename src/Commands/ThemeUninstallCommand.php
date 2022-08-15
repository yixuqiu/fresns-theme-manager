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
    protected $signature = 'theme:uninstall {name}';

    protected $description = 'Install the theme from the specified path';

    public function handle()
    {
        try {
            $unikey = $this->argument('name');

            $this->call('theme:unpublish', [
                'name' => $unikey,
            ]);

            $theme = new Theme($unikey);
            File::deleteDirectory($theme->getThemePath());

            // Triggers top-level computation of composer.json hash values and installation of extension themes
            @exec('composer update');

            $this->info("Uninstalled: {$unikey}");
        } catch (\Throwable $e) {
            $this->error("Uninstall fail: {$e->getMessage()}");
        }

        return 0;
    }
}
