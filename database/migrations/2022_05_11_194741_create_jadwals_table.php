<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJadwalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jadwals', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable();
            $table->bigInteger('sekolah_id');
            $table->string('name');
            $table->dateTime('start')->nullable();
            $table->dateTime('end')->nullable();
            $table->smallInteger('duration')->nullable();
            $table->smallInteger('soal_count')->nullable();
            $table->boolean('shuffle')->default(false);
            $table->boolean('show_score')->default(false);
            $table->boolean('active')->default(false);
            $table->json('opt')->nullable();
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
        Schema::dropIfExists('jadwals');
    }
}
