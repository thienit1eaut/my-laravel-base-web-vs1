<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // nullable: tài khoản đã có sẵn chưa có username, sẽ được cập nhật sau
            $table->string('username', 50)->nullable()->unique()->after('name');
            $table->foreignId('id_role')->nullable()->after('password')
                ->constrained('roles')->restrictOnDelete();
            $table->boolean('is_active')->default(true)->after('id_role');
            $table->string('image')->nullable()->after('is_active');
            $table->text('note')->nullable()->after('image');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['id_role']);
            $table->dropUnique(['username']);
            $table->dropColumn(['username', 'id_role', 'is_active', 'image', 'note']);
        });
    }
};