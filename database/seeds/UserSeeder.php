<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       // Insert some stuff
        DB::table('users')->insert(
            array(
                'id' => 1,
                'username' => 'Mambetali Nokisbaev',
                'email' => 'admin@example.com',
                'password' => '$2y$10$IFj6SwqC0Sxrsiv4YkCt.OJv1UV4mZrWuyLoRG7qt47mseP9mJ58u', // 123456
                'avatar' => 'no_avatar.png',
                'role_users_id' => 1,
                'is_all_warehouses' => 1,
                'status' => 1,
            )
        );
        $user = User::findOrFail(1);
        $user->assignRole(1);
    }
}
