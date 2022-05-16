<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemSoalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_soals', function (Blueprint $table) {
            $table->bigInteger('soal_id');
            $table->string('type');
            $table->string('num')->nullable();
            $table->text('text');
            $table->double('score')->nullable();
            $table->json('options')->nullable();
            $table->json('corrects')->nullable();
            $table->json('relations')->nullable();
            $table->json('labels')->nullable();
            $table->boolean('shuffle')->default(false);
            $table->text('answer')->nullable();
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
        Schema::dropIfExists('item_soals');
    }
}
