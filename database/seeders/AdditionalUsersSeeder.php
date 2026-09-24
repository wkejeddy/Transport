<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdditionalUsersSeeder extends Seeder
{
    public function run(): void
    {
        // 20 Sample Passengers for Real Voyage
        $passengers = [
            ['name' => 'Franck Nguema', 'email' => 'franck.nguema@gmail.com', 'phone' => '+237 691 000 101'],
            ['name' => 'Clarisse Mbah', 'email' => 'clarisse.mbah@yahoo.fr', 'phone' => '+237 671 000 102'],
            ['name' => 'Eric Tchakounte', 'email' => 'eric.tchakounte@gmail.com', 'phone' => '+237 691 000 103'],
            ['name' => 'Bernadette Fotso', 'email' => 'bernadette.fotso@gmail.com', 'phone' => '+237 671 000 104'],
            ['name' => 'Ibrahim Bello', 'email' => 'ibrahim.bello@gmail.com', 'phone' => '+237 691 000 105'],
            ['name' => 'Sophie Kamga', 'email' => 'sophie.kamga@yahoo.fr', 'phone' => '+237 671 000 106'],
            ['name' => 'Patrick Ebode', 'email' => 'patrick.ebode@gmail.com', 'phone' => '+237 691 000 107'],
            ['name' => 'Aissatou Mohamadou', 'email' => 'aissatou.m@gmail.com', 'phone' => '+237 671 000 108'],
            ['name' => 'Georges Moundi', 'email' => 'georges.moundi@yahoo.fr', 'phone' => '+237 691 000 109'],
            ['name' => 'Chantal Atangana', 'email' => 'chantal.atangana@gmail.com', 'phone' => '+237 671 000 110'],
            ['name' => 'Rodrigue Foka', 'email' => 'rodrigue.foka@gmail.com', 'phone' => '+237 691 000 111'],
            ['name' => 'Carine Nken', 'email' => 'carine.nken@yahoo.fr', 'phone' => '+237 671 000 112'],
            ['name' => 'David Abena', 'email' => 'david.abena@gmail.com', 'phone' => '+237 691 000 113'],
            ['name' => 'Fatimatou Saidou', 'email' => 'fatimatou.saidou@gmail.com', 'phone' => '+237 671 000 114'],
            ['name' => 'Christian Eboa', 'email' => 'christian.eboa@yahoo.fr', 'phone' => '+237 691 000 115'],
            ['name' => 'Sylvie Nana', 'email' => 'sylvie.nana@gmail.com', 'phone' => '+237 671 000 116'],
            ['name' => 'Brice Menanga', 'email' => 'brice.menanga@gmail.com', 'phone' => '+237 691 000 117'],
            ['name' => 'Vanessa Kome', 'email' => 'vanessa.kome@yahoo.fr', 'phone' => '+237 671 000 118'],
            ['name' => 'Herve Owona', 'email' => 'herve.owona@gmail.com', 'phone' => '+237 691 000 119'],
            ['name' => 'Grace Fonkou', 'email' => 'grace.fonkou@gmail.com', 'phone' => '+237 671 000 120'],
        ];

        foreach ($passengers as $p) {
            User::firstOrCreate(
                ['email' => $p['email']],
                [
                    'name' => $p['name'],
                    'phone' => $p['phone'],
                    'role' => 'passager',
                    'status' => 'active',
                    'wallet_balance' => rand(5000, 30000),
                    'password' => Hash::make('password123'),
                ]
            );
        }
    }
}
