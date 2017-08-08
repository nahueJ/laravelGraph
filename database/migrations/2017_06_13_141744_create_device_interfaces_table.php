<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class CreateDeviceInterfacesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('device_interfaces', function (Blueprint $table) {
            $table->increments('id');

			$table->unsignedInteger('aparato_id')->index();
		    $table->foreign('aparato_id')->references('id')->on('aparatos');

			$table->mediumText('interface_type');
			$table->string('OID');
			$table->ipAddress('ip');
			$table->ipAddress('mascara');
			$table->ipAddress('subred');
			$table->mediumText('estado_negociacion')->nullable();
			$table->boolean('full_duplex')->nullable();
			
			$table->unsignedInteger('interface_pair_id')->nullable()->index();
		    $table->foreign('interface_pair_id')->references('id')->on('device_interfaces');

			$table->string('estado')->nullable();

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
        Schema::dropIfExists('device_interfaces');
    }
}
