<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('infaks')) {
            Schema::table('infaks', function (Blueprint $table) {
                if (!Schema::hasColumn('infaks', 'pembayaran_id')) {
                    $table->foreignId('pembayaran_id')
                        ->nullable()
                        ->after('keterangan')
                        ->constrained('pembayarans')
                        ->nullOnDelete();
                }

                if (!Schema::hasColumn('infaks', 'metode_bayar')) {
                    $table->string('metode_bayar', 30)
                        ->nullable()
                        ->after('pembayaran_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('infaks')) {
            Schema::table('infaks', function (Blueprint $table) {
                if (Schema::hasColumn('infaks', 'pembayaran_id')) {
                    $table->dropConstrainedForeignId('pembayaran_id');
                }
                if (Schema::hasColumn('infaks', 'metode_bayar')) {
                    $table->dropColumn('metode_bayar');
                }
            });
        }
    }
};

