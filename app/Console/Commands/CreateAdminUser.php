<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create
                            {email? : Email du compte admin}
                            {password? : Mot de passe (>= 10 caracteres)}
                            {--name=Admin DINOR : Nom affiche}';

    protected $description = 'Cree ou met a jour un compte admin pour le panneau Filament.';

    public function handle(): int
    {
        $email = $this->argument('email') ?: $this->ask('Email admin');
        $password = $this->argument('password') ?: $this->secret('Mot de passe (>= 10 caracteres)');
        $name = (string) $this->option('name');

        $validator = Validator::make(
            ['email' => $email, 'password' => $password],
            [
                'email' => ['required', 'email', 'max:150'],
                'password' => ['required', 'string', 'min:10', 'max:128'],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return self::FAILURE;
        }

        $user = User::updateOrCreate(
            ['email' => strtolower(trim($email))],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ],
        );

        $this->info("Compte admin pret : {$user->email}");

        return self::SUCCESS;
    }
}
