<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            // Add a decimal column for currency totals
            $table->decimal('total', 12, 2)
                  ->unsigned()
                  ->default(0)
                  ->after('meta');
        });
    }

    public function down(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            $table->dropColumn('total');
        });
    }
};
