<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 'name', 'mobile', 'country', 'city', 'email', 'zip',
        // rus lang generated
        DB::table('warehouses')->insert([
            [
                'id' => 1,
                'name' => 'Склад 1',
                'mobile' => '998901234567',
                'country' => 'Узбекистан',
                'city' => 'Ташкент',
                'email' => 'admin@ecorme.uz',
                'zip' => '100000',
            ],
        ]);
    }
}
