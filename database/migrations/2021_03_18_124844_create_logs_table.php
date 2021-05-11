<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('sqlite')->create('logs', function (Blueprint $table) {
            $table->id();
            $table->text('request')
                  ->nullable();
            $table->text('response')
                  ->nullable();
            $table->string('ip');
            $table->boolean('login_result');
            $table->boolean('sent')
                  ->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('sqlite')->dropIfExists('logs');
    }
}
