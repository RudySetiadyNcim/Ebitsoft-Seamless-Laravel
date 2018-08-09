<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserTipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_tips', function (Blueprint $table) {
            $table->increments('id');
            $table->string('game');
            $table->timestamp('transaction_at');
            $table->string('user_id');
            $table->decimal('amount', 15, 2);
            $table->string('transaction_id')->unique();
            $table->string('refId')->unique();
            $table->string('product_id');
            $table->string('tips')->nullable();
            $table->string('table_id')->nullable();
            $table->string('game_identifier')->nullable();
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
        Schema::dropIfExists('user_tips');
    }
}
