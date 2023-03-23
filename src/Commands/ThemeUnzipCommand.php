<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace Fresns\ThemeManager\Commands;

use Fresns\ThemeManager\Support\Json;
use Fresns\ThemeManager\Support\Zip;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class ThemeUnzipCommand extends Command
{
    protected $signature = 'theme:unzip {path}';

    protected $description = 'Unzip the theme to the theme directory';

    public function handle()
    {
        $this->zip = new Zip();

        $tmpDirPath = $this->zip->unpack($this->argument('path'));

        $themeJsonPath = "{$tmpDirPath}/theme.json";
        if (! file_exists($tmpDirPath)) {
            \info($message = 'Theme file does not exist: '.$themeJsonPath);
            $this->error('install theme error '.$message);

            return Command::FAILURE;
        }

        $theme = Json::make($themeJsonPath);

        $themeUnikey = $theme->get('unikey');
        if (! $themeUnikey) {
            \info('Failed to get theme unikey: '.var_export($themeUnikey, true));
            $this->error('install theme error, theme.json is invalid theme json');

            return Command::FAILURE;
        }

        $themeDir = sprintf('%s/%s',
            config('themes.paths.themes'),
            $themeUnikey
        );

        if (file_exists($themeDir)) {
            $this->backup($themeDir, $themeUnikey);
        }

        File::copyDirectory($tmpDirPath, $themeDir);
        File::deleteDirectory($tmpDirPath);

        Cache::put('install:theme_unikey', $themeUnikey, now()->addMinutes(5));

        return Command::SUCCESS;
    }

    public function backup(string $themeDir, string $themeUnikey)
    {
        $backupDir = config('themes.paths.backups');

        File::ensureDirectoryExists($backupDir);

        $dirs = File::glob("$backupDir/$themeUnikey*");

        $currentBackupCount = count($dirs);

        $targetPath = sprintf('%s/%s-%s-%s', $backupDir, $themeUnikey, date('YmdHis'), $currentBackupCount + 1);

        File::copyDirectory($themeDir, $targetPath);
        File::cleanDirectory($themeDir);

        return true;
    }
}
