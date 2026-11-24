<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('siswas')) {
            return;
        }

        Schema::table('siswas', function (Blueprint $table) {
            if (!Schema::hasColumn('siswas', 'status')) {
                $table->string('status', 20)->default('aktif')->after('kelas_id');
                $table->index('status', 'siswas_status_index');
            }

            if (!Schema::hasColumn('siswas', 'tanggal_keluar')) {
                $table->date('tanggal_keluar')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('siswas')) {
            return;
        }

        Schema::table('siswas', function (Blueprint $table) {
            if (Schema::hasColumn('siswas', 'status')) {
                $table->dropIndex('siswas_status_index');
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('siswas', 'tanggal_keluar')) {
                $table->dropColumn('tanggal_keluar');
            }
        });
    }
};

