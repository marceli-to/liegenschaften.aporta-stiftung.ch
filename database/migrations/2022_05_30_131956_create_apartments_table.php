<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApartmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('apartments', function (Blueprint $table) {
          $table->id();
          $table->string('uuid', 36);
          $table->string('number', 20);
          $table->string('description', 255)->nullable();
          $table->decimal('size', 8, 1)->nullable()->default(0.0);
          $table->decimal('size_terrace', 8, 1)->nullable()->default(0.0);
          $table->decimal('size_patio', 8, 1)->nullable()->default(0.0);
          $table->decimal('size_balcony', 8, 1)->nullable()->default(0.0);
          $table->smallInteger('order')->default(-1);
          $table->tinyInteger('publish')->default(1);
          $table->foreignId('room_id')->constrained();
          $table->foreignId('state_id')->constrained();
          $table->foreignId('estate_id')->constrained();
          $table->foreignId('floor_id')->constrained();
          $table->foreignId('building_id')->constrained();
          $table->unsignedBigInteger('tenant_id')->nullable();
          $table->foreign('tenant_id')->references('id')->on('tenants');
          $table->softDeletes();
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
        Schema::dropIfExists('apartments');
    }
}
