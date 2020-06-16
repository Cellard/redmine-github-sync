<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (DB::table('users')->where('email', 'pm@101media.ru')->count() == 0) {
            factory(\App\User::class)->create(['email' => 'pm@101media.ru', 'name' => 'Cellard']);
        }

        if (DB::table('users')->where('email', 'ok@101media.ru')->count() == 0) {
            factory(\App\User::class)->create(['email' => 'ok@101media.ru', 'name' => 'Kirill']);
        }
    }
}
