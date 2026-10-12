<?php

namespace Database\Seeders;

use App\Models\Proposal;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Proposal::factory()->count(25)->create();
    }
}
