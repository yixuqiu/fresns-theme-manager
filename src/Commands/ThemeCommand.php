<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace Fresns\ThemeManager\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class ThemeCommand extends Command
{
    protected $signature = 'theme';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all available commands';

    /**
     * @var string
     */
    public static $logo = <<<LOGO
  ________                           __  ___                                 
 /_  __/ /_  ___  ____ ___  ___     /  |/  /___ _____  ____ _____ ____  _____
  / / / __ \/ _ \/ __ `__ \/ _ \   / /|_/ / __ `/ __ \/ __ `/ __ `/ _ \/ ___/
 / / / / / /  __/ / / / / /  __/  / /  / / /_/ / / / / /_/ / /_/ /  __/ /    
/_/ /_/ /_/\___/_/ /_/ /_/\___/  /_/  /_/\__,_/_/ /_/\__,_/\__, /\___/_/     
                                                          /____/             
LOGO;

    public function handle(): void
    {
        $this->info(static::$logo);

        $this->comment('');
        $this->comment('Available commands:');
        $this->listAdminCommands();
    }

    protected function listAdminCommands(): void
    {
        $commands = collect(Artisan::all())->mapWithKeys(function ($command, $key) {
            if (Str::startsWith($key, 'theme')) {
                return [$key => $command];
            }

            return [];
        })->toArray();

        $width = $this->getColumnWidth($commands);

        /** @var Command $command */
        foreach ($commands as $command) {
            $this->info(sprintf(" %-{$width}s %s", $command->getName(), $command->getDescription()));
        }
    }

    private function getColumnWidth(array $commands): int
    {
        $widths = [];

        foreach ($commands as $command) {
            $widths[] = static::strlen($command->getName());
            foreach ($command->getAliases() as $alias) {
                $widths[] = static::strlen($alias);
            }
        }

        return $widths ? max($widths) + 2 : 0;
    }

    /**
     * Returns the length of a string, using mb_strwidth if it is available.
     *
     * @param  string  $string  The string to check its length
     * @return int The length of the string
     */
    public static function strlen($string): int
    {
        if (false === $encoding = mb_detect_encoding($string, null, true)) {
            return strlen($string);
        }

        return mb_strwidth($string, $encoding);
    }
}
