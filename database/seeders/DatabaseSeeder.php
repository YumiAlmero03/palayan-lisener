<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Server;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $user = new User();
        $user->updateOrInsert(
            [
            'name' => 'Admin',
            'email' => 'Admin@email.com',
            ],
        [
            'password' => bcrypt('12345'),
        ]);

        $server = new Server();

        $server->insert([
            'server_name' => 'local',
            'server_url' => 'http://www.palayan.com/',
        ]);
        
        $server->insert([
            'server_name' => 'dev',
            'server_url' => 'http://89.117.63.249/dev/',
        ]);
    }
}
