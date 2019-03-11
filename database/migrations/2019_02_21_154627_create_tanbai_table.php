<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTanbaiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tanbai', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('receive_user_id');
            $table->unsignedInteger('send_user_id');
            $table->string('content');
            $table->string('signature', 32)->comment('署名');
            $table->softDeletes()->comment('预留软删除')->nullable();
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
        Schema::dropIfExists('tanbai');
    }
}
