<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use App\Models\Biometric;

class ConnectBiometrics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'connect:biometrics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reconnect to Biometrics';

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
        $biometrics = Biometric::where('is_active',1)->get();
        foreach ($biometrics as $value) {
            try {
                $process = new Process(['ping',$value->bio_ip]);
                $process->run();
                $output = $process->getOutput();
                $value->testBiometric();
                $this->info($value->bio_ip . ' is connected');
            } catch (\Throwable $th) {
                $this->info($value->bio_ip . ' is not connected');
            }
            
        }
        return 'test';
    }
}
