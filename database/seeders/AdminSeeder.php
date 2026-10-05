<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Development credentials for initial setup.
     * IMPORTANT: Change this password immediately in production environments!
     */
    public function run(): void
    {
        Admin::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'     => 'System Administrator',
                'password' => Hash::make('password'),
            ]
        );
    }
}
