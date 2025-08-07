<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = Hash::make('12345');

        DB::table('users')->insert([
            [
                'username' => 'admin',
                'password' => $password,
                'name' => 'Admin',
                'active' => true,
                'role' => User::Role_Admin,
            ],
            [
                'username' => 'operator',
                'password' => $password,
                'name' => 'Operator',
                'active' => true,
                'role' => User::Role_Operator,
            ]
        ]);
    }
}
