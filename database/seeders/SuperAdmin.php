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
        $user->name = 'Super Admin';
        $user->email = 'admin@ujianq.id';
        $user->email_verified_at = now();
        $user->password = bcrypt('passwordAdmin');
        $user->role = null;
        $user->save();
    }
}
