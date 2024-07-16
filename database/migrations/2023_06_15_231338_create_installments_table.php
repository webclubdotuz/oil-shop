<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInstallmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('installments', function (Blueprint $table) {
            $table->id();
            $table->integer('client_id')->index('installments_client_id');
            $table->integer('sale_id')->index('installments_sale_id');
            // $table->integer('installment_info_id')->index('installments_installment_info_id');
            $table->foreignId('installment_info_id')->constrained()->onUpdate('RESTRICT')->onDelete('RESTRICT');

            $table->decimal('amount', 20, 2);
            $table->date('date');
            $table->enum('status', ['paid', 'unpaid', 'partial'])->default('unpaid');
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
        Schema::dropIfExists('installments');
    }
}
