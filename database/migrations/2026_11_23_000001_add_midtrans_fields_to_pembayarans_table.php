<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pembayarans')) {
            Schema::table('pembayarans', function (Blueprint $table) {
                if (!Schema::hasColumn('pembayarans', 'gateway_transaction_id')) {
                    $table->string('gateway_transaction_id', 120)->nullable()->after('qr_payload');
                }
                if (!Schema::hasColumn('pembayarans', 'qr_image_url')) {
                    $table->text('qr_image_url')->nullable()->after('gateway_transaction_id');
                }
                if (!Schema::hasColumn('pembayarans', 'gateway_status')) {
                    $table->string('gateway_status', 40)->nullable()->after('qr_image_url');
                }
                if (!Schema::hasColumn('pembayarans', 'gateway_response')) {
                    $table->json('gateway_response')->nullable()->after('gateway_status');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pembayarans')) {
            Schema::table('pembayarans', function (Blueprint $table) {
                foreach (['gateway_response', 'gateway_status', 'qr_image_url', 'gateway_transaction_id'] as $col) {
                    if (Schema::hasColumn('pembayarans', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};

