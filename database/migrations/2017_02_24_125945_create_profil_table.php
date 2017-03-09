<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProfilTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('profils', function (Blueprint $table) {
            $table->increments('id_profils');
            $table->timestamps();
            $table->text('description');
            $table->integer('sexe');
            $table->integer('orientation');
            $table->integer('cheveux');
            $table->integer('yeux');
            $table->integer('poid');
            $table->float('taille',3,2);
            $table->integer('users_id');
            $table->string('pseudo');
            $table->string('mail');
            $table->string('nom');
            $table->string('prenom');
            $table->bigInteger('birthday');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('profils');
    }
}
