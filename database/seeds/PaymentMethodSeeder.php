<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       	// Insert some stuffs
        DB::table('payment_methods')->insert(
            array([
                'id'           => 1,
                'title'        => 'Наличные',
                'is_default'   => 0,

            ],
            [
                'id'           => 2,
                'title'        => 'Перевод',
                'is_default'   => 1,

            ],
            [
                'id'           => 3,
                'title'        => 'Пластиковая карта',
                'is_default'   => 0,

            ],
            [
                'id'           => 4,
                'title'        => 'Click',
                'is_default'   => 1,

            ]
            )

        );
    }
}
