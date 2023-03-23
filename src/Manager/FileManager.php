<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace Fresns\ThemeManager\Manager;

use Fresns\ThemeManager\Support\Json;

class FileManager
{
    protected $file;

    protected $status = [];

    public function __construct()
    {
        $this->file = config('themes.manager.default.file');

        $this->themesJson = Json::make($this->file);

        $this->status = $this->themesJson->get('themes');
    }

    public function all()
    {
        return $this->status;
    }

    public function install(string $theme)
    {
        $this->status[$theme] = false;

        return $this->write();
    }

    public function uninstall(string $theme)
    {
        unset($this->status[$theme]);

        return $this->write();
    }

    public function activate(string $theme)
    {
        $this->status[$theme] = true;

        return $this->write();
    }

    public function deactivate(string $theme)
    {
        $this->status[$theme] = false;

        return $this->write();
    }

    public function isActivate(string $theme)
    {
        if (array_key_exists($theme, $this->status)) {
            return $this->status[$theme] == true;
        }

        return false;
    }

    public function isDeactivate(string $theme)
    {
        return ! $this->isActivate($theme);
    }

    public function write(): bool
    {
        $data = $this->themesJson->get();
        $data['themes'] = $this->status;

        try {
            $content = json_encode(
                $data,
                \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE | \JSON_PRETTY_PRINT | \JSON_FORCE_OBJECT
            );

            return (bool) file_put_contents($this->file, $content);
        } catch (\Throwable $e) {
            info('Failed to update theme status: %s'.$e->getMessage());

            return false;
        }
    }
}
