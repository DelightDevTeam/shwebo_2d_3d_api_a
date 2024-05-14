<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'id'             => 1,
                'name'           => 'Admin',
                'phone'          => '0911111111',
                'password'       => '$2y$10$qyxYm.2dlaXROvs0OrGHseo4qbeissRMqNWdhlcr/vUqE62vN94Fi', // password
                'agent_id'       => 1,
                'remember_token' => null,
                'created_at'     => '2019-09-10 14:00:26',
                'updated_at'     => '2019-09-10 14:00:26',
            ],
            [
                'id'             => 2,
                'name'           => 'User',
                'phone'          => '0922222222',
                'password'       => '$2y$10$qyxYm.2dlaXROvs0OrGHseo4qbeissRMqNWdhlcr/vUqE62vN94Fi', // password
                'agent_id'       => 1,
                'remember_token' => null,
                'created_at'     => '2023-08-14 14:00:26',
                'updated_at'     => '2023-08-14 14:00:26',
            ]
           

        ];

        User::insert($users);
    }
}