<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // [2.1.12] Last login tracking
            $table->timestamp('last_login_at')->nullable()->after('password');
            $table->timestamp('last_failed_login_at')->nullable()->after('last_login_at');

            // [2.1.11] Password age enforcement (must be ≥1 day old before change)
            $table->timestamp('password_changed_at')->nullable()->after('last_failed_login_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['last_login_at', 'last_failed_login_at', 'password_changed_at']);
        });
    }
};
