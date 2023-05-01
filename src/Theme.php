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
    protected $themeFskey;

    /**
     * @var FileManager
     */
    protected $manager;

    public function __construct(?string $themeFskey = null)
    {
        $this->manager = new FileManager();

        $this->setThemeName($themeFskey);
    }

    public function config(string $key, $default = null)
    {
        return config('themes.'.$key, $default);
    }

    public function setThemeName(?string $themeFskey = null)
    {
        $this->themeFskey = $themeFskey;
    }

    public function getFskey()
    {
        return $this->getStudlyName();
    }

    public function getLowerName(): string
    {
        return Str::lower($this->themeFskey);
    }

    public function getStudlyName()
    {
        return Str::studly($this->themeFskey);
    }

    public function getKebabName()
    {
        return Str::kebab($this->themeFskey);
    }

    public function getSnakeName()
    {
        return Str::snake($this->themeFskey);
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
        $themeFskey = $this->getStudlyName();

        return "{$path}/{$themeFskey}";
    }

    public function getAssetsPath(): ?string
    {
        if (! $this->exists()) {
            return null;
        }

        $path = $this->config('paths.assets');
        $themeFskey = $this->getStudlyName();

        return "{$path}/{$themeFskey}";
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
        if (! $themeFskey = $this->getStudlyName()) {
            return false;
        }

        if (in_array($themeFskey, $this->all())) {
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
            $themeFskey = basename(dirname($themeJson));

            if (! $this->isValidTheme($themeFskey)) {
                continue;
            }

            if (! $this->isAvailableTheme($themeFskey)) {
                continue;
            }

            $themes[] = $themeFskey;
        }

        return $themes;
    }

    public function isValidTheme(?string $themeFskey = null)
    {
        if (! $themeFskey) {
            $themeFskey = $this->getStudlyName();
        }

        if (! $themeFskey) {
            return false;
        }

        $path = $this->config('paths.themes');

        $themeJsonPath = sprintf('%s/%s/theme.json', $path, $themeFskey);

        $themeJson = Json::make($themeJsonPath);

        return $themeFskey == $themeJson->get('fskey');
    }

    public function isAvailableTheme(?string $themeFskey = null)
    {
        if (! $themeFskey) {
            $themeFskey = $this->getStudlyName();
        }

        if (! $themeFskey) {
            return false;
        }

        try {
            // Verify that the program is loaded correctly by loading the program
            $theme = new Theme($themeFskey);
        } catch (\Throwable $e) {
            \info("{$themeFskey} registration failed, not a valid theme");

            return false;
        }

        return true;
    }

    public function manualAddNamespace()
    {
        $fskey = $this->getStudlyName();
        if (! $fskey) {
            return;
        }

        if (file_exists(base_path('/vendor/autoload.php'))) {
            /** @var \Composer\Autoload\ClassLoader $loader */
            $loader = require base_path('/vendor/autoload.php');

            $namespaces = config('themes.namespaces', []);

            foreach ($namespaces as $namespace => $paths) {
                $appPaths = array_map(function ($path) use ($fskey) {
                    return "{$path}/{$fskey}/app";
                }, $paths);
                $loader->addPsr4("{$namespace}\\{$fskey}\\", $appPaths, true);

                $factoryPaths = array_map(function ($path) use ($fskey) {
                    return "{$path}/{$fskey}/database/factories";
                }, $paths);
                $loader->addPsr4("{$namespace}\\{$fskey}\\Database\\Factories\\", $factoryPaths, true);

                $seederPaths = array_map(function ($path) use ($fskey) {
                    return "{$path}/{$fskey}/database/seeders";
                }, $paths);
                $loader->addPsr4("{$namespace}\\{$fskey}\\Database\\Seeders\\", $seederPaths, true);

                $testPaths = array_map(function ($path) use ($fskey) {
                    return "{$path}/{$fskey}/tests";
                }, $paths);
                $loader->addPsr4("{$namespace}\\{$fskey}\\Tests\\", $testPaths, true);
            }
        }
    }

    public function getThemeInfo()
    {
        // Validation: Does the directory name and fskey match correctly
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
