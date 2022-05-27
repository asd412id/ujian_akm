<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePesertasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pesertas', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('sekolah_id');
            $table->string('uid')->unique();
            $table->string('password');
            $table->string('password_string');
            $table->string('name');
            $table->char('jk')->default('L');
            $table->string('token', 40)->unique();
            $table->string('ruang')->nullable();
            $table->string('remember_token')->nullable();
            $table->boolean('is_login')->default(false);
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
        Schema::dropIfExists('pesertas');
    }
}
