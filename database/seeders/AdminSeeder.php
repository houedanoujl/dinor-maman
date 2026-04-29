<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');
        $name = env('ADMIN_NAME', 'Admin DINOR');

        if (! $email || ! $password) {
            $this->command->error('ADMIN_EMAIL et ADMIN_PASSWORD doivent être définis dans le .env.');
            return;
        }

        if (strlen($password) < 10) {
            $this->command->error('ADMIN_PASSWORD doit contenir au moins 10 caractères.');
            return;
        }

        $user = User::updateOrCreate(
            ['email' => strtolower(trim($email))],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ],
        );

        $this->command->info("Compte admin prêt : {$user->email}");
    }
}
