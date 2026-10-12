<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Demo accounts, both with the password "password". Change them before real use.
     */
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'proponent@example.com'],
            ['name' => 'Demo Proponent', 'password' => 'password', 'role' => UserRole::Proponent],
        );

        User::query()->firstOrCreate(
            ['email' => 'reviewer@example.com'],
            ['name' => 'Demo Reviewer', 'password' => 'password', 'role' => UserRole::Reviewer],
        );
    }
}
