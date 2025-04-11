<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
    */
    public function run()
    {
        DB::table('admins')->insert([
            'first_name' => 'Admin',
            'last_name' => 'Account',
            'email' => 'adminaccount@gmail.com',
            'contact_number' => '09698865798',
            'password' => Hash::make('adminpassword123'), 
            'created_at' => now(), 
            'updated_at' => now(),  
        ]);
    }
}
