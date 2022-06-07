<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdmin extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::whereNull('role')->delete();
        $user = new User();
        $user->name = 'asd412id';
        $user->email = 'asd412id@gmail.com';
        $user->email_verified_at = now();
        $user->password = bcrypt('Terbuk4lah#');
        $user->role = null;
        $user->save();
    }
}
