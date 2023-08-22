<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use App\Models\Biometric;

class ConnectOneBiometric extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'connect:biometric {ping}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check one biometric ';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $ping = (string)$this->argument('ping');
        try {
            $process = new Process(['ping',$ping]);
            $process->run();
            $output = $process->getOutput();
            Biometric::testBiometric($ping);
            $this->info($ping . ' can connect');
        } catch (\Throwable $th) {
            $this->info($ping . ' cannot connect');
        }
    }
}
