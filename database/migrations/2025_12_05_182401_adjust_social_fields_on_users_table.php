<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Make sure password can be nullable for social accounts (optional)
            // If password column already exists and is NOT nullable, you can change it:
            // $table->string('password')->nullable()->change();

            // Add or modify provider/provider_id with safe lengths
            // provider: short name (google, facebook) -> 50 chars is plenty
            // provider_id: provider-specific id -> 100 chars is safe
            if (! Schema::hasColumn('users', 'provider')) {
                $table->string('provider', 50)->nullable()->after('password');
            } else {
                $table->string('provider', 50)->nullable()->change();
            }

            if (! Schema::hasColumn('users', 'provider_id')) {
                $table->string('provider_id', 100)->nullable()->after('provider');
            } else {
                $table->string('provider_id', 100)->nullable()->change();
            }

            // Add composite unique index (fits under typical key limits)
            // Use a named index so you can drop it easily later if needed
            $table->unique(['provider', 'provider_id'], 'users_provider_provider_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop the unique index if it exists
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = array_map(fn($i) => $i->getName(), $sm->listTableIndexes($table->getTable()));

            if (in_array('users_provider_provider_id_unique', $indexes, true)) {
                $table->dropUnique('users_provider_provider_id_unique');
            }

            if (Schema::hasColumn('users', 'provider_id')) {
                $table->dropColumn('provider_id');
            }
            if (Schema::hasColumn('users', 'provider')) {
                $table->dropColumn('provider');
            }

            // Optionally revert password nullability if you changed it earlier
            // $table->string('password')->nullable(false)->change();
        });
    }
};
