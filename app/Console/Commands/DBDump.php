<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use RuntimeException;
use Symfony\Component\Process\Process;

class DBDump extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:dump';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dump database';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info("Dumping database");

        $mysqlDump = env('DB_MYSQL_DUMP');
        $db = env('DB_DATABASE');
        $user = env('DB_USERNAME');
        $pass = env('DB_PASSWORD');

        if (empty($mysqlDump) || empty($db) || empty($user) || empty($pass)) {
            $this->error('Not enough parameters to run DB dumping');
            return -1;
        }

        $dir = storage_path('mysql');
        $filename = $db . '_' . Carbon::now()->format('Y_m_d_H_i') . '.sql.gz';

        $command = "$mysqlDump -u $user -p$pass $db | gzip > $dir/$filename";
        $this->line("running: $mysqlDump -u $user -p*** $db | gzip> $dir/$filename");

        $process = Process::fromShellCommandline($command);
        $processOutput = '';

        $captureOutput = function ($type, $line) use (&$processOutput) {
            $processOutput .= $line;
        };
        $process->setTimeout(null)->run($captureOutput);


        if ($process->getExitCode()) {
            $exception = new RuntimeException("$mysqlDump -u $user -p**** $db | gzip> $dir/$filename" . " - " . $processOutput);
            report($exception);
            $this->error($processOutput);
        } else {
            $this->info('Database dumped to ' . $filename);
        }

        return $process->getExitCode();
    }
}
