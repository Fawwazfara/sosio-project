<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;

class MidtransWebhookController extends Controller
{
    public function handle(Request $request, PaymentGatewayService $gateway)
    {
        $orderId = (string) $request->input('order_id', '');
        $statusCode = (string) $request->input('status_code', '');
        $grossAmount = (string) $request->input('gross_amount', '');
        $signatureKey = (string) $request->input('signature_key', '');

        if (!$gateway->verifyMidtransSignature($orderId, $statusCode, $grossAmount, $signatureKey)) {
            return response()->json(['message' => 'Invalid Midtrans signature.'], 403);
        }

        $pembayaran = Pembayaran::with(['siswa', 'kelas.wali_kelas'])
            ->where('kode_referensi', $orderId)
            ->first();

        if (!$pembayaran) {
            return response()->json(['message' => 'Transaksi tidak ditemukan.'], 404);
        }

        try {
            $updated = $gateway->applyMidtransStatus($pembayaran, $request->all());
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Webhook Midtrans diproses.',
            'status' => $updated->status,
            'gateway_status' => $updated->gateway_status,
        ]);
    }
}

