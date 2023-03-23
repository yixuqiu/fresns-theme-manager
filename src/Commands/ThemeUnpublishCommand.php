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

class ThemeUnpublishCommand extends Command
{
    use Traits\WorkThemeNameTrait;

    protected $signature = 'theme:unpublish {name}';

    protected $description = 'Distribute static resources of the theme';

    public function handle()
    {
        $themeName = $this->getThemeName();
        $theme = new Theme($themeName);

        if (! $theme->isValidTheme()) {
            return Command::FAILURE;
        }

        File::deleteDirectory($theme->getAssetsPath());

        $this->info("Unpublished: {$theme->getUnikey()}");

        return Command::SUCCESS;
    }
}
