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
        Schema::table('candidates', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by_admin_id')->nullable()->after('photo');
            $table->unsignedBigInteger('updated_by_admin_id')->nullable()->after('created_by_admin_id');

            $table->foreign('created_by_admin_id')->references('id')->on('admins')->onDelete('set null');
            $table->foreign('updated_by_admin_id')->references('id')->on('admins')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropForeign(['created_by_admin_id']);
            $table->dropForeign(['updated_by_admin_id']);
            $table->dropColumn(['created_by_admin_id', 'updated_by_admin_id']);
        });
    }
};
