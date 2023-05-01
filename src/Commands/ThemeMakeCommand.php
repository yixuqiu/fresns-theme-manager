<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace Fresns\ThemeManager\Commands;

use Illuminate\Console\Command;

class ThemeMakeCommand extends Command
{
    protected $signature = 'theme:make {fskey}
        {--force}
        ';

    protected $description = 'Alias of new command';

    public function handle()
    {
        return $this->call('new-theme', [
            'fskey' => $this->argument('fskey'),
            '--force' => $this->option('force'),
        ]);
    }
}
