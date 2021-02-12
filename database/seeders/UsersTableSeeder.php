<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'created_at' => Carbon::now(),
            'first_name' => 'Administrator',
            'password' => password_hash('admin', PASSWORD_DEFAULT),
            'login' => 'admin',
            'is_admin' => 1
        ]);
    }
}
