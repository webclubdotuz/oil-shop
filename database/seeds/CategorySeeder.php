<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 'code', 'name',
        // rus lang generated
        DB::table('categories')->insert(
            array(
                [
                    'id' => 1,
                    'code' => '001',
                    'name' => 'Телефоны',
                ],
                [
                    'id' => 2,
                    'code' => '002',
                    'name' => 'Ноутбуки',
                ],
                [
                    'id' => 3,
                    'code' => '003',
                    'name' => 'Телевизоры',
                ],
                [
                    'id' => 4,
                    'code' => '004',
                    'name' => 'Аксессуары',
                ],
                [
                    'id' => 5,
                    'code' => '005',
                    'name' => 'Компьютеры',
                ]
            )
        );
    }
}
