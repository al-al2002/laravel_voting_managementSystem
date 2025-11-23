<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove unused columns that are no longer needed
            $table->dropColumn(['skipped_elections', 'manual_eligibility', 'admin_override']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Restore columns if migration is rolled back
            $table->unsignedInteger('skipped_elections')->default(0)->after('eligibility_overridden');
            $table->boolean('manual_eligibility')->nullable()->after('is_eligible');
            $table->boolean('admin_override')->nullable()->after('is_eligible');
        });
    }
};
