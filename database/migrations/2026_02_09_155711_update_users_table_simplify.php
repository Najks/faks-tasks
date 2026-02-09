<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // ADD
            $table->string('username')->unique()->after('id');
            $table->string('role')->default('user')->after('email');

            // DROP
            $table->dropColumn([
                'password',
                'email_verified_at',
                'remember_token',
            ]);
        });

        // DROP unused tables
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // RESTORE dropped columns
            $table->string('password');
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();

            // REMOVE added columns
            $table->dropColumn(['username', 'role']);
        });

        // RESTORE tables
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }
};
