<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePesertaTestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('peserta_tests', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('jadwal_id');
            $table->bigInteger('peserta_id');
            $table->bigInteger('soal_id');
            $table->bigInteger('login_id');
            $table->bigInteger('item_soal_id');
            $table->string('type');
            $table->text('text');
            $table->double('score')->nullable();
            $table->json('option')->nullable();
            $table->json('correct')->nullable();
            $table->json('relation')->nullable();
            $table->text('answer')->nullable();
            $table->double('pscore')->nullable();
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
        Schema::dropIfExists('peserta_tests');
    }
}
