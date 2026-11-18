<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pembayarans')) {
            Schema::create('pembayarans', function (Blueprint $table) {
                $table->id();
                $table->string('kode_referensi')->unique();
                $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
                $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
                $table->foreignId('wali_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('metode', 20); // online | offline
                $table->string('channel', 40)->nullable(); // qris_sandbox | tunai_wali_kelas
                $table->string('status', 30)->default('pending'); // pending | waiting_offline | paid | cancelled | expired
                $table->json('bulan_list');
                $table->integer('tahun');
                $table->integer('nominal_per_bulan');
                $table->integer('total_nominal');
                $table->text('qr_payload')->nullable();
                $table->text('catatan')->nullable();
                $table->timestamp('expired_at')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamps();

                $table->index(['siswa_id', 'status']);
                $table->index(['kelas_id', 'status']);
                $table->index(['wali_user_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
    }
};

