<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use App\Models\Biometric;

class ConnectBioUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'connect:biousers {ping}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check one biometric users';

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
            dd(Biometric::listUser($ping));
        } catch (\Throwable $th) {
            $this->info($ping . ' cannot connect');
            $this->info($th);
        }
    }
}
