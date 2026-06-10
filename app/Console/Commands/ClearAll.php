<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class ClearAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Clearing application cache...');
        Artisan::call('cache:clear');

        $this->info('Clearing config cache...');
        Artisan::call('config:clear');

        $this->info('Clearing route cache...');
        Artisan::call('route:clear');

        $this->info('Clearing view cache...');
        Artisan::call('view:clear');

        $this->info('Clearing compiled classes...');
        Artisan::call('clear-compiled');

        $this->info('Clearing logs...');
        $logPath = storage_path('logs');
        $files = File::glob($logPath . '/*.log'); // Get all log files

        foreach ($files as $file) {
            file_put_contents($file, ''); // Empty the log files
        }

        $this->info('All caches and logs have been cleared successfully!');
    }
}
