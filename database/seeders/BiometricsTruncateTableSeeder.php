<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Biometric;

class BiometricsTruncateTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Biometric::truncate();
    }
}
