<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace Fresns\ThemeManager\Commands\Traits;

trait WorkThemeNameTrait
{
    public function getThemeName()
    {
        $themeName = $this->argument('name');
        if (! $themeName) {
            $themeRootPath = config('themes.paths.themes');
            if (str_contains(getcwd(), $themeRootPath)) {
                $themeName = basename(getcwd());
            }
        }

        return $themeName;
    }

    public function validateThemeRootPath($theme)
    {
        $themeRootPath = config('themes.paths.themes');
        $currentThemeRootPath = rtrim($theme->getThemePath(), '/');

        return $themeRootPath == $currentThemeRootPath;
    }
}
