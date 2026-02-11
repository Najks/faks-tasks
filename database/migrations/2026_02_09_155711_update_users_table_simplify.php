<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username');
            }

            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('user');
            }

            $drop = [];
            foreach (['password', 'email_verified_at', 'remember_token'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $drop[] = $col;
                }
            }
            if ($drop) {
                $table->dropColumn($drop);
            }
        });

        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }

    public function down(): void
    {
        // Restore tables first (optional), then columns.
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

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'password')) {
                $table->string('password');
            }
            if (!Schema::hasColumn('users', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable();
            }
            if (!Schema::hasColumn('users', 'remember_token')) {
                $table->rememberToken();
            }

            $drop = [];
            foreach (['username', 'role'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $drop[] = $col;
                }
            }
            if ($drop) {
                $table->dropColumn($drop);
            }
        });
    }
};
