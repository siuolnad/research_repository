<?php

namespace Database\Seeders;

use App\Models\Proposal;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(UserSeeder::class);

        $proponent = User::query()->where('email', 'proponent@example.com')->firstOrFail();

        Proposal::factory()->count(25)->create(['user_id' => $proponent->id]);
    }
}
