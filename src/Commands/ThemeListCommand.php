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

class ThemeListCommand extends Command
{
    protected $signature = 'theme:list';

    protected $description = 'Get the list of installed themes';

    public function handle()
    {
        $themeDir = config('themes.paths.themes');

        $themeDirs = File::glob(sprintf('%s/*', rtrim($themeDir, '/')));

        $rows = [];
        foreach ($themeDirs as $themeDir) {
            if (! is_dir($themeDir)) {
                continue;
            }

            $themeName = basename($themeDir);

            $theme = new Theme($themeName);

            $rows[] = $theme->getThemeInfo();
        }

        $this->table([
            'Theme Name',
            'Validation',
            'Available',
            'Theme Status',
            'Assets Status',
            'Theme Path',
            'Assets Path',
        ], $rows);

        return 0;
    }

    public function replaceDir(?string $path)
    {
        if (! $path) {
            return null;
        }

        return ltrim(str_replace(base_path(), '', $path), '/');
    }
}
