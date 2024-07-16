<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 'name', 'description', 'image',
        // rus lang generated
        DB::table('brands')->insert(
            array(
                [
                    'id' => 1,
                    'name' => 'Samsung',
                    'description' => 'Samsung description',
                    'image' => 'image_default.png',
                ],
                [
                    'id' => 2,
                    'name' => 'Apple',
                    'description' => 'Apple description',
                    'image' => 'image_default.png',
                ],
                [
                    'id' => 3,
                    'name' => 'Xiaomi',
                    'description' => 'Xiaomi description',
                    'image' => 'image_default.png',
                ],
                [
                    'id' => 4,
                    'name' => 'Huawei',
                    'description' => 'Huawei description',
                    'image' => 'image_default.png',
                ],
                [
                    'id' => 5,
                    'name' => 'Artel',
                    'description' => 'Artel description',
                    'image' => 'image_default.png',
                ]
            )
        );
    }
}
