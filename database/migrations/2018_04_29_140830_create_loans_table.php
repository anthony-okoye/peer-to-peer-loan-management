<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan', function (Blueprint $table) {
            $table->increments('id');
            $table-> integer('user_id')->default(0);
            $table-> text('description');
            $table-> integer('amount');
            $table-> integer('period');
            $table-> float('rate');
            $table-> float('total');
            $table-> float('payback')->nullable();
            $table->string('status')->nullable();
            $table->date('deadline')->nullable();
            $table->string('paymentStatus')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loan');
    }
}
