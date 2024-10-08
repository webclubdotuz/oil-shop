<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToInstallmentInfosTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('installment_infos', function(Blueprint $table)
		{
			// $table->foreign('role_users_id', 'users_role_users_id')->references('id')->on('roles')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('client_id')->references('id')->on('clients')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('sale_id')->references('id')->on('sales')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('users', function(Blueprint $table)
		{
			$table->dropForeign('users_role_users_id');
		});
	}

}
