<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePhotoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('photos', function (Blueprint $table) {
            $table->increments('id_photos');
            $table->timestamps();
            $table->string('name');
            $table->string('type', 10);
            $table->string('small');
            $table->boolean('valid');
            $table->boolean('sup');
            $table->boolean('profil');
            $table->integer('vote');
            $table->integer('profils_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('photos');
    }
}
