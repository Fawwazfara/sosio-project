<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('siswas') && !Schema::hasColumn('siswas', 'wali_user_id')) {
            Schema::table('siswas', function (Blueprint $table) {
                $table->foreignId('wali_user_id')
                    ->nullable()
                    ->after('wa_wali_2')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('siswas') && Schema::hasColumn('siswas', 'wali_user_id')) {
            Schema::table('siswas', function (Blueprint $table) {
                $table->dropConstrainedForeignId('wali_user_id');
            });
        }
    }
};

