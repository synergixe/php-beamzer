<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
 
class CreateStreamNotificaitionTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(){
    
        Schema::create('stream_notifications', function (Blueprint $table){
            $table->('id');
            $table->text('data');
            $table->string('type', 220);
            $table->datetime('read_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
    
        Schema::drop('stream_notifications');
    }
}
