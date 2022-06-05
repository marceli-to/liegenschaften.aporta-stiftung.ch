<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCollectionItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('collection_items', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36);
            $table->date('sent_at')->nullable();
            $table->date('read_at')->nullable();
            $table->date('replied_at')->nullable();
            $table->tinyInteger('accepted')->default(0);
            $table->text('comment')->nullable();
            $table->foreignId('apartment_id')->constrained();
            $table->foreignId('collection_id')->constrained();
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
        Schema::dropIfExists('collection_items');
    }
}
