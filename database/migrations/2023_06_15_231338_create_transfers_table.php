<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransfersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('transfers', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('user_id')->index('user_id_transfers');
			$table->string('Ref', 192);
			$table->dateTime('date');
			$table->integer('from_warehouse_id')->index('from_warehouse_id');
			$table->integer('to_warehouse_id')->index('to_warehouse_id');
			$table->float('items', 10, 0);
			$table->float('tax_rate', 10, 0)->nullable()->default(0);
			$table->float('TaxNet', 10, 0)->nullable()->default(0);
			$table->float('discount', 10, 0)->nullable()->default(0);
			$table->string('discount_type', 192);
			$table->float('discount_percent_total', 10, 0)->nullable()->default(0);
			$table->float('shipping', 10, 0)->nullable()->default(0);
			$table->float('GrandTotal', 10, 0)->default(0);
			$table->string('statut', 192);
			$table->text('notes')->nullable();
			$table->timestamps(6);
			$table->softDeletes();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('transfers');
	}

}
