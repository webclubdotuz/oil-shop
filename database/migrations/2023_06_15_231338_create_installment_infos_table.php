<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInstallmentInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('installment_infos', function (Blueprint $table) {
            $table->id();
            $table->integer('client_id')->index('installments_client_id');
            $table->integer('sale_id')->index('installments_sale_id');

            $table->decimal('percentage', 3, 2);
            $table->integer('months');

            $table->enum('status', ['paid', 'partial'])->default('partial');

            $table->text('notes')->nullable();

            $table->timestamps();
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
        Schema::dropIfExists('installment_infos');
    }
}
