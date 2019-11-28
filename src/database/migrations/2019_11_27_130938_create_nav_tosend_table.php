<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNavTosendTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nav_tosend', function (Blueprint $table) {
            $table->increments('id');
            $table->string('status',100);
            $table->integer('user_id');
            $table->string('invoice_id',100);
            $table->string('customer',255);
            $table->mediumText('xml');
            $table->string('operation',100);
            $table->text('msg');
            $table->string('transaction_id',100);
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
        Schema::drop('nav_tosend');
    }
}
