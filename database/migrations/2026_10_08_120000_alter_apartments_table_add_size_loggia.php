<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::table('apartments', function (Blueprint $table) {
      $table->decimal('size_loggia', 8, 1)->nullable()->default(0.0)->after('size_balcony');
    });
  }

  public function down(): void
  {
    Schema::table('apartments', function (Blueprint $table) {
      $table->dropColumn('size_loggia');
    });
  }
};
