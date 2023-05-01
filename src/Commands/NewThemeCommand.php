<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace Fresns\ThemeManager\Commands;

use Fresns\ThemeManager\Support\Config\GenerateConfigReader;
use Fresns\ThemeManager\Support\Process;
use Fresns\ThemeManager\Theme;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class NewThemeCommand extends Command
{
    use Traits\StubTrait;

    protected $signature = 'new-theme {fskey}
        {--force}
        ';

    protected $description = 'Create a new laravel theme';

    /**
     * The laravel filesystem instance.
     *
     * @var Filesystem
     */
    protected $filesystem;

    protected $theme;

    /**
     * @var string
     */
    protected $themeFskey;

    /**
     * Execute the console command.
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function handle()
    {
        $this->filesystem = $this->laravel['files'];
        $this->themeFskey = Str::afterLast($this->argument('fskey'), '/');

        $this->theme = new Theme($this->themeFskey);

        // clear directory or exit when theme exists.
        if (File::exists($this->theme->getThemePath())) {
            if (! $this->option('force')) {
                $this->error("Theme {$this->theme->getFskey()} exists");

                return Command::FAILURE;
            }

            File::deleteDirectory($this->theme->getThemePath());
        }

        $this->generateFolders();
        $this->generateFiles();

        // composer dump-autoload
        Process::run('composer dump-autoload', $this->output);

        $this->info("Theme [{$this->themeFskey}] created successfully");

        return Command::SUCCESS;
    }

    /**
     * Get the list of folders will created.
     *
     * @return array
     */
    public function getFolders()
    {
        return config('themes.paths.generator');
    }

    /**
     * Generate the folders.
     */
    public function generateFolders()
    {
        foreach ($this->getFolders() as $key => $folder) {
            $folder = GenerateConfigReader::read($key);

            if ($folder->generate() === false) {
                continue;
            }

            if ($folder->inMulti() === false) {
                continue;
            }

            $path = config('themes.paths.themes').'/'.$this->argument('fskey').'/'.$folder->getPath();

            $this->filesystem->makeDirectory($path, 0755, true);
            if (config('themes.stubs.gitkeep')) {
                $this->generateGitKeep($path);
            }
        }
    }

    /**
     * Generate git keep to the specified path.
     *
     * @param  string  $path
     */
    public function generateGitKeep($path)
    {
        $this->filesystem->put($path.'/.gitkeep', '');
    }

    /**
     * Remove git keep from the specified path.
     *
     * @param  string  $path
     */
    public function removeParentDirGitKeep(string $path)
    {
        if (config('themes.stubs.gitkeep')) {
            $dirName = dirname($path);
            if (count($this->filesystem->glob("$dirName/*")) >= 1) {
                $this->filesystem->delete("$dirName/.gitkeep");
            }
        }
    }

    /**
     * Get the list of files will created.
     *
     * @return array
     */
    public function getFiles()
    {
        return config('themes.stubs.files');
    }

    /**
     * Generate the files.
     */
    public function generateFiles()
    {
        foreach ($this->getFiles() as $stub => $file) {
            $themeFskey = $this->argument('fskey');

            $path = config('themes.paths.themes').'/'.$themeFskey.'/'.$file;

            if ($keys = $this->getReplaceKeys($path)) {
                $file = $this->getReplacedContent($file, $keys);
                $path = $this->getReplacedContent($path, $keys);
            }

            if (! $this->filesystem->isDirectory($dir = dirname($path))) {
                $this->filesystem->makeDirectory($dir, 0775, true);
                $this->removeParentDirGitKeep($dir);
            }

            $this->filesystem->put($path, $this->getStubContents($stub));
            $this->removeParentDirGitKeep($path);

            $this->info("Created : {$path}");
        }
    }
}
