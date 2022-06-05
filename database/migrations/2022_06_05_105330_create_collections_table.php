<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCollectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36);
            $table->string('name', 255);
            $table->string('firstname', 255);
            $table->string('email', 255);
            $table->date('sent_at')->nullable();
            $table->date('read_at')->nullable();
            $table->date('replied_at')->nullable();
            $table->tinyInteger('accepted')->default(0);
            $table->text('comment')->nullable();
            $table->text('error')->nullable();
            $table->foreignId('estate_id')->constrained();
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
        Schema::dropIfExists('collections');
    }
}
