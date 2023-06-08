<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace Fresns\ThemeManager\Commands;

use Fresns\ThemeManager\Theme;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ThemeUninstallCommand extends Command
{
    use Traits\WorkThemeFskeyTrait;

    protected $signature = 'theme:uninstall {fskey}';

    protected $description = 'Install the theme from the specified path';

    public function handle()
    {
        try {
            $themeFskey = $this->getThemeFskey();
            $theme = new Theme($themeFskey);

            if ($this->validateThemeRootPath($theme)) {
                $this->error('Failed to operate themes root path');

                return Command::FAILURE;
            }

            event('theme:uninstalling', [[
                'fskey' => $themeFskey,
            ]]);

            $this->call('theme:unpublish', [
                'fskey' => $themeFskey,
            ]);

            File::deleteDirectory($theme->getThemePath());

            event('theme:uninstalled', [[
                'fskey' => $themeFskey,
            ]]);

            $this->info("Uninstalled: {$themeFskey}");
        } catch (\Throwable $e) {
            info("Uninstall fail: {$e->getMessage()}");
            $this->error("Uninstall fail: {$e->getMessage()}");

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
