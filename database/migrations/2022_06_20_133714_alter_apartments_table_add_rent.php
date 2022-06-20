<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterApartmentsTableAddRent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('apartments', function (Blueprint $table) {
        $table->decimal('rent_net', 8, 2)->default(0.00)->after('tenant_id');
        $table->decimal('additional_cost', 8, 2)->default(0.00)->after('rent_net');
        $table->decimal('rent_gross', 8, 2)->default(0.00)->after('additional_cost');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
