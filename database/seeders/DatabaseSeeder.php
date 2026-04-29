<?php

namespace Database\Seeders;

use App\Models\Participant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (! app()->environment('local')) {
            return;
        }

        User::updateOrCreate([
            'email' => 'test@example.com',
        ], [
            'name' => 'Test User',
            'password' => Hash::make('password'),
        ]);

        $participants = [
            ['Awa', 'Kone', '+2250700000001', 'Cocody', 'awa@example.com', 186, now()->subDays(2)],
            ['Mariam', 'Traore', '+2250700000002', 'Yopougon', 'mariam@example.com', 164, now()->subDays(3)],
            ['Fatou', 'Bamba', '+2250700000003', 'Marcory', 'fatou@example.com', 141, now()->subDays(4)],
            ['Nadia', 'Coulibaly', '+2250700000004', 'Abobo', 'nadia@example.com', 98, now()->subDays(5)],
            ['Estelle', 'Nguessan', '+2250700000005', 'Plateau', 'estelle@example.com', 77, now()->subDays(6)],
            ['Ines', 'Diaby', '+2250700000006', 'Treichville', 'ines@example.com', 52, now()->subDays(7)],
            ['Sarah', 'Yao', '+2250700000007', 'Bingerville', 'sarah@example.com', 31, now()->subDays(8)],
            ['Grace', 'Amani', '+2250700000008', 'Koumassi', 'grace@example.com', 18, now()->subDays(9)],
        ];

        foreach ($participants as [$firstName, $lastName, $phone, $city, $email, $votes, $approvedAt]) {
            Participant::updateOrCreate([
                'phone' => $phone,
            ], [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'city' => $city,
                'email' => $email,
                'status' => Participant::STATUS_APPROVED,
                'vote_count' => $votes,
                'approved_at' => $approvedAt,
            ]);
        }
    }
}
