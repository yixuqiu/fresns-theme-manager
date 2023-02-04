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

class ThemePublishCommand extends Command
{
    use Traits\WorkPluginNameTrait;

    protected $signature = 'theme:publish {name}';

    protected $description = 'Distribute static resources of the theme';

    public function handle()
    {
        $themeName = $this->getPluginName();
        $theme = new Theme($themeName);

        if (! $theme->isValidTheme()) {
            return Command::FAILURE;
        }

        File::cleanDirectory($theme->getAssetsPath());
        File::copyDirectory($theme->getAssetsSourcePath(), $theme->getAssetsPath());

        $this->info("Published: {$theme->getUnikey()}");

        return Command::SUCCESS;
    }
}
