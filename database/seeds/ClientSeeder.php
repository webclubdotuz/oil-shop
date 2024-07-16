<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 'id','user_id','username','code','email','city','phone','address','status','photo', 'credit_limit'

        // 1-id По умолчанию client, credit_limit 0

        DB::table('clients')->insert(
            array(
                [
                    'id' => 1,
                    'user_id' => 1,
                    'username' => 'По умолчанию',
                    'code' => '1',
                    'email' => null,
                    'city' => null,
                    'phone' => null,
                    'address' => null,
                    'status' => 1,
                    'photo' => null,
                    'credit_limit' => 0
                ]
            )
        );



    }
}
