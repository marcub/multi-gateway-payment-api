<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('user')->after('password');
            }

            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('role');
            }

            if (Schema::hasColumn('users', 'name')) {
                $table->dropColumn('name');
            }

            if (Schema::hasColumn('users', 'email_verified_at')) {
                $table->dropColumn('email_verified_at');
            }

            if (Schema::hasColumn('users', 'remember_token')) {
                $table->dropColumn('remember_token');
            }
        });

        DB::statement('ALTER TABLE users MODIFY id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE users DROP PRIMARY KEY');
        DB::statement('ALTER TABLE users DROP COLUMN id');
        DB::statement('ALTER TABLE users ADD COLUMN id CHAR(36) NOT NULL FIRST');
        DB::statement('ALTER TABLE users ADD PRIMARY KEY (id)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE users DROP PRIMARY KEY');
        DB::statement('ALTER TABLE users DROP COLUMN id');
        DB::statement('ALTER TABLE users ADD COLUMN id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT FIRST');
        DB::statement('ALTER TABLE users ADD PRIMARY KEY (id)');

        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }

            if (Schema::hasColumn('users', 'is_active')) {
                $table->dropColumn('is_active');
            }

            if (!Schema::hasColumn('users', 'name')) {
                $table->string('name')->nullable()->after('id');
            }

            if (!Schema::hasColumn('users', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('email');
            }

            if (!Schema::hasColumn('users', 'remember_token')) {
                $table->rememberToken();
            }
        });
    }
};
