<?php

namespace App\Console\Commands;

use App\Helpers\Fiscal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MoveFiscals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'move:fiscals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refactor unfoldered fiscals';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $dirs = Storage::disk('fiscal')->directories();

        foreach ($dirs as $dir) {
            $files = Storage::disk('fiscal')->files($dir);
            foreach ($files as $file) {
                $info = pathinfo($file);
                $newFile = Fiscal::path($info['dirname'], $info['filename']);
                Storage::disk('fiscal')->move($file, $newFile);
            }
        }

        return 0;
    }
}
