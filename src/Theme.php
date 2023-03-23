<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace Fresns\ThemeManager;

use Fresns\ThemeManager\Manager\FileManager;
use Fresns\ThemeManager\Support\Json;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class Theme
{
    protected $themeName;

    /**
     * @var FileManager
     */
    protected $manager;

    public function __construct(?string $themeName = null)
    {
        $this->manager = new FileManager();

        $this->setThemeName($themeName);
    }

    public function config(string $key, $default = null)
    {
        return config('themes.'.$key, $default);
    }

    public function setThemeName(?string $themeName = null)
    {
        $this->themeName = $themeName;
    }

    public function getUnikey()
    {
        return $this->getStudlyName();
    }

    public function getLowerName(): string
    {
        return Str::lower($this->themeName);
    }

    public function getStudlyName()
    {
        return Str::studly($this->themeName);
    }

    public function getKebabName()
    {
        return Str::kebab($this->themeName);
    }

    public function getSnakeName()
    {
        return Str::snake($this->themeName);
    }

    public function getClassNamespace()
    {
        $namespace = $this->config('namespace');
        $namespace .= '\\'.$this->getStudlyName();
        $namespace = str_replace('/', '\\', $namespace);

        return trim($namespace, '\\');
    }

    public function getThemePath(): ?string
    {
        $path = $this->config('paths.themes');
        $themeName = $this->getStudlyName();

        return "{$path}/{$themeName}";
    }

    public function getAssetsPath(): ?string
    {
        if (! $this->exists()) {
            return null;
        }

        $path = $this->config('paths.assets');
        $themeName = $this->getStudlyName();

        return "{$path}/{$themeName}";
    }

    public function getAssetsSourcePath(): ?string
    {
        if (! $this->exists()) {
            return null;
        }

        $path = $this->getThemePath();

        return "{$path}/assets";
    }

    public function getComposerJsonPath(): ?string
    {
        $path = $this->getThemePath();

        return "{$path}/composer.json";
    }

    public function getThemeJsonPath(): ?string
    {
        $path = $this->getThemePath();

        return "{$path}/theme.json";
    }

    public function exists(): bool
    {
        if (! $themeName = $this->getStudlyName()) {
            return false;
        }

        if (in_array($themeName, $this->all())) {
            return true;
        }

        return false;
    }

    public function all(): array
    {
        $path = $this->config('paths.themes');
        $themeJsons = File::glob("$path/**/theme.json");

        $themes = [];
        foreach ($themeJsons as $themeJson) {
            $themeName = basename(dirname($themeJson));

            if (! $this->isValidTheme($themeName)) {
                continue;
            }

            if (! $this->isAvailableTheme($themeName)) {
                continue;
            }

            $themes[] = $themeName;
        }

        return $themes;
    }

    public function isValidTheme(?string $themeName = null)
    {
        if (! $themeName) {
            $themeName = $this->getStudlyName();
        }

        if (! $themeName) {
            return false;
        }

        $path = $this->config('paths.themes');

        $themeJsonPath = sprintf('%s/%s/theme.json', $path, $themeName);

        $themeJson = Json::make($themeJsonPath);

        return $themeName == $themeJson->get('unikey');
    }

    public function isAvailableTheme(?string $themeName = null)
    {
        if (! $themeName) {
            $themeName = $this->getStudlyName();
        }

        if (! $themeName) {
            return false;
        }

        try {
            // Verify that the program is loaded correctly by loading the program
            $theme = new Theme($themeName);
        } catch (\Throwable $e) {
            \info("{$themeName} registration failed, not a valid theme");

            return false;
        }

        return true;
    }

    public function manualAddNamespace()
    {
        $unikey = $this->getStudlyName();
        if (! $unikey) {
            return;
        }

        if (file_exists(base_path('/vendor/autoload.php'))) {
            /** @var \Composer\Autoload\ClassLoader $loader */
            $loader = require base_path('/vendor/autoload.php');

            $namespaces = config('themes.namespaces', []);

            foreach ($namespaces as $namespace => $paths) {
                $appPaths = array_map(function ($path) use ($unikey) {
                    return "{$path}/{$unikey}/app";
                }, $paths);
                $loader->addPsr4("{$namespace}\\{$unikey}\\", $appPaths, true);

                $factoryPaths = array_map(function ($path) use ($unikey) {
                    return "{$path}/{$unikey}/database/factories";
                }, $paths);
                $loader->addPsr4("{$namespace}\\{$unikey}\\Database\\Factories\\", $factoryPaths, true);

                $seederPaths = array_map(function ($path) use ($unikey) {
                    return "{$path}/{$unikey}/database/seeders";
                }, $paths);
                $loader->addPsr4("{$namespace}\\{$unikey}\\Database\\Seeders\\", $seederPaths, true);

                $testPaths = array_map(function ($path) use ($unikey) {
                    return "{$path}/{$unikey}/tests";
                }, $paths);
                $loader->addPsr4("{$namespace}\\{$unikey}\\Tests\\", $testPaths, true);
            }
        }
    }

    public function getThemeInfo()
    {
        // Validation: Does the directory name and unikey match correctly
        // Available: Whether the service provider is registered successfully
        $item['Theme Name'] = "<info>{$this->getStudlyName()}</info>";
        $item['Validation'] = $this->isValidTheme() ? '<info>true</info>' : '<fg=red>false</fg=red>';
        $item['Available'] = $this->isAvailableTheme() ? '<info>Available</info>' : '<fg=red>Unavailable</fg=red>';
        $item['Assets Status'] = file_exists($this->getAssetsPath()) ? '<info>Published</info>' : '<fg=red>Unpublished</fg=red>';
        $item['Theme Path'] = $this->replaceDir($this->getThemePath());
        $item['Assets Path'] = $this->replaceDir($this->getAssetsPath());

        return $item;
    }

    public function replaceDir(?string $path)
    {
        if (! $path) {
            return null;
        }

        return ltrim(str_replace(base_path(), '', $path), '/');
    }

    public function __toString()
    {
        return $this->getStudlyName();
    }
}
