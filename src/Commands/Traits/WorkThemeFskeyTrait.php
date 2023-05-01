<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace Fresns\ThemeManager\Commands\Traits;

trait WorkThemeFskeyTrait
{
    public function getThemeFskey()
    {
        $themeFskey = $this->argument('fskey');
        if (! $themeFskey) {
            $themeRootPath = config('themes.paths.themes');
            if (str_contains(getcwd(), $themeRootPath)) {
                $themeFskey = basename(getcwd());
            }
        }

        return $themeFskey;
    }

    public function validateThemeRootPath($theme)
    {
        $themeRootPath = config('themes.paths.themes');
        $currentThemeRootPath = rtrim($theme->getThemePath(), '/');

        return $themeRootPath == $currentThemeRootPath;
    }
}
