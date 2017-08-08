<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddJerarquiaColumnToNodo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('nodos', function (Blueprint $table) {
            $table->unsignedInteger('jerarquia')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nodos', function (Blueprint $table) {
            $table->dropColumn('jerarquia');
        });
    }
}
