<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // 'name', 'ShortName', 'base_unit', 'operator', 'operator_value', 'is_active',
        // rus lang generated
        DB::table('units')->insert(
            array(
                [
                    'id' => 1,
                    'name' => 'Штук',
                    'ShortName' => 'шт',
                ],
                [
                    'id' => 2,
                    'name' => 'Килограмм',
                    'ShortName' => 'кг',
                ],
                [
                    'id' => 3,
                    'name' => 'Метр',
                    'ShortName' => 'м',
                ],
            )
            );

    }
}
