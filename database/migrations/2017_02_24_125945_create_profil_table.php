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
            $table->text('description')->nullable();
            $table->integer('sexe')->nullable();
            $table->integer('orientation')->nullable();
            $table->integer('cheveux')->nullable();
            $table->integer('yeux')->nullable();
            $table->integer('poid')->nullable();
            $table->float('taille',3,2)->nullable();
            $table->integer('users_id')->nullable();
            $table->string('pseudo')->nullable();
            $table->string('mail')->nullable();
            $table->string('nom')->nullable();
            $table->string('prenom')->nullable();
            $table->bigInteger('birthday')->nullable();
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
