<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeNodosAddLegacyNodoId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('nodos', function (Blueprint $table) {
            //
            $table->integer('legacy_nodo_id')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('aparatos', function (Blueprint $table) {
            $table->dropColumn('legacy_nodo_id');
        });
    }
}
