<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('profile_photo')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        // Migrate existing admin users to admins table
        DB::statement("
            INSERT INTO admins (id, name, email, password, profile_photo, remember_token, created_at, updated_at)
            SELECT id, name, email, password, profile_photo, remember_token, created_at, updated_at
            FROM users
            WHERE role = 'admin'
        ");

        // Remove admin users from users table
        DB::table('users')->where('role', 'admin')->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Migrate admins back to users table
        DB::statement("
            INSERT INTO users (id, name, email, password, profile_photo, role, remember_token, created_at, updated_at)
            SELECT id, name, email, password, profile_photo, 'admin', remember_token, created_at, updated_at
            FROM admins
        ");

        Schema::dropIfExists('admins');
    }
};
