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
        $zip = new Zip();

        $tmpDirPath = $zip->unpack($this->argument('path'));

        $themeJsonPath = "{$tmpDirPath}/theme.json";
        if (! file_exists($tmpDirPath)) {
            \info($message = 'Theme file does not exist: '.$themeJsonPath);
            $this->error('install theme error '.$message);

            return Command::FAILURE;
        }

        $theme = Json::make($themeJsonPath);

        $themeFskey = $theme->get('fskey');
        if (! $themeFskey) {
            \info('Failed to get theme fskey: '.var_export($themeFskey, true));
            $this->error('install theme error, theme.json is invalid theme json');

            return Command::FAILURE;
        }

        $themeDir = sprintf('%s/%s',
            config('themes.paths.themes'),
            $themeFskey
        );

        if (file_exists($themeDir)) {
            $this->backup($themeDir, $themeFskey);
        }

        File::copyDirectory($tmpDirPath, $themeDir);
        File::deleteDirectory($tmpDirPath);

        Cache::put('install:theme_fskey', $themeFskey, now()->addMinutes(5));

        return Command::SUCCESS;
    }

    public function backup(string $themeDir, string $themeFskey)
    {
        $backupDir = config('themes.paths.backups');

        File::ensureDirectoryExists($backupDir);

        if (! is_file($backupDir.'/.gitignore')) {
            file_put_contents($backupDir.'/.gitignore', '*'.PHP_EOL.'!.gitignore');
        }

        $dirs = File::glob("$backupDir/$themeFskey*");

        $currentBackupCount = count($dirs);

        $targetPath = sprintf('%s/%s-%s-%s', $backupDir, $themeFskey, date('YmdHis'), $currentBackupCount + 1);

        File::copyDirectory($themeDir, $targetPath);
        File::cleanDirectory($themeDir);

        return true;
    }
}
