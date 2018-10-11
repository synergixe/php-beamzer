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
    
        Schema::create('tbl_stream_notifications', function (Blueprint $table){
            $table->char('id', 36)->primary();
            $table->string('type', 220);
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
    
        Schema::drop('tbl_stream_notifications');
    }
}
