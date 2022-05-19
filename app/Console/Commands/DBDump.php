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
        $mysqlDump = env('DB_MYSQL_DUMP');
        $db = env('DB_DATABASE');
        $user = env('DB_USERNAME');
        $pass = env('DB_PASSWORD');
        $dir = storage_path('mysql');
        $filename = $db . '_' . Carbon::now()->format('Y_m_d_H_i') . '.sql.gz';
        $flags = '--add-drop-table --add-locks --extended-insert --quick --force --triggers --routines --events --set-gtid-purged=off --no-tablespaces';

        if (empty($mysqlDump) || empty($db) || empty($user) || empty($pass)) {
            $this->error('Not enough parameters to run DB dumping');
        }

        $command = "$mysqlDump -u $user -p$pass $flags $db | gzip > $dir/$filename";
        $this->info("running: $mysqlDump -u $user -p*** $flags $db | gzip> $dir/$filename");

        $process = Process::fromShellCommandline($command);
        $processOutput = '';
        $captureOutput = function ($type, $line) use (&$processOutput) {
            $processOutput .= $line;
        };
        $process->setTimeout(null)->run($captureOutput);

        $this->info($processOutput);

        if ($process->getExitCode()) {
            $exception = new RuntimeException("$mysqlDump -u $user -p**** $flags $db | gzip> $dir/$filename" . " - " . $processOutput);
            report($exception);

            $this->error($exception->getMessage());
        } else {
            $this->info('Database dumped to ' . $filename);
        }

        return $process->getExitCode();
    }
}
